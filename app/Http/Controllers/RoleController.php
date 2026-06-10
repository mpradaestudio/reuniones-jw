<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\DestroyRoleRequest;
use App\Http\Requests\Roles\DuplicateRoleRequest;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Models\Role;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Módulo Roles y Permisos.
 *
 * Reglas:
 *  - Roles GLOBALes; solo gestiona quien tenga `roles.manage` (SuperAdministrador).
 *  - Roles de sistema protegidos: no se renombran ni eliminan.
 *  - El SuperAdministrador conserva SIEMPRE todos los permisos.
 *  - Eliminar un rol con usuarios exige reasignarlos a otro rol (un rol por usuario);
 *    no se permite reasignar al rol global SuperAdministrador.
 *  - Cada acción de escritura registra un evento en `audit_logs`.
 */
class RoleController extends Controller
{
    /**
     * Etiquetas legibles por módulo (prefijo del permiso).
     */
    private const MODULE_LABELS = [
        'dashboard' => 'Panel',
        'congregations' => 'Congregaciones',
        'users' => 'Usuarios',
        'roles' => 'Roles',
    ];

    private function superAdminRoleName(): string
    {
        return config('tenancy.super_admin_role', 'SuperAdministrador');
    }

    // =====================================================================
    //  Lectura (vistas)
    // =====================================================================

    /**
     * Listado de roles con nº de permisos, nº de usuarios e indicador
     * Sistema/Personalizado.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        return view('roles.index', ['roles' => $roles]);
    }

    /**
     * Detalle de un rol: permisos agrupados por módulo y usuarios asignados.
     */
    public function show(Role $role): View
    {
        $this->authorize('view', $role);

        $role->load(['permissions', 'users']);

        return view('roles.show', [
            'role' => $role,
            'groupedPermissions' => $this->groupPermissionNames($role->permissions->pluck('name')->all()),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('roles.create', $this->formData());
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $role->load('permissions');

        return view('roles.edit', array_merge($this->formData(), [
            'role' => $role,
            'rolePermissions' => $role->permissions->pluck('name')->all(),
            'isSuperAdminRole' => $role->name === $this->superAdminRoleName(),
        ]));
    }

    /**
     * Formulario para duplicar un rol (clonar permisos).
     */
    public function duplicateForm(Role $role): View
    {
        $this->authorize('duplicate', $role);

        return view('roles.duplicate', [
            'role' => $role,
            'permissionsCount' => $role->permissions()->count(),
        ]);
    }

    /**
     * Asistente de eliminación: si el rol tiene usuarios, ofrece seleccionar el
     * rol destino para reasignarlos antes de eliminar.
     */
    public function confirmDelete(Role $role): View
    {
        $this->authorize('delete', $role);

        return view('roles.delete', [
            'role' => $role,
            'usersCount' => $role->users()->count(),
            'targets' => $this->assignableTargetRoles($role),
        ]);
    }

    // =====================================================================
    //  Escritura
    // =====================================================================

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $permissions = $data['permissions'] ?? [];

        $role = DB::transaction(function () use ($data, $permissions) {
            $role = new Role;
            $role->name = $data['name'];
            $role->guard_name = 'web';
            $role->is_system = false;
            $role->description = $data['description'] ?? null;
            $role->save();

            $role->syncPermissions($permissions);

            AuditLogger::record('role.created', $role, [], [
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => array_values($permissions),
            ]);

            return $role;
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', "Rol «{$role->name}» creado correctamente.");
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();
        $isSuperAdminRole = $role->name === $this->superAdminRoleName();

        DB::transaction(function () use ($data, $role, $isSuperAdminRole) {
            $before = [
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
            ];

            // El SuperAdministrador conserva siempre TODOS los permisos.
            $permissions = $isSuperAdminRole
                ? Permission::pluck('name')->all()
                : ($data['permissions'] ?? []);

            // El nombre de los roles de sistema es inmutable (validado en el request).
            if (! $role->isSystem()) {
                $role->name = $data['name'];
            }
            $role->description = $data['description'] ?? null;
            $role->save();

            $role->syncPermissions($permissions);

            $after = [
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
            ];

            $changedOld = [];
            $changedNew = [];
            foreach ($after as $field => $value) {
                if ($before[$field] !== $value) {
                    $changedOld[$field] = $before[$field];
                    $changedNew[$field] = $value;
                }
            }

            AuditLogger::record('role.updated', $role, $changedOld, $changedNew);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', "Rol «{$role->name}» actualizado correctamente.");
    }

    public function duplicate(DuplicateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        $clone = DB::transaction(function () use ($data, $role) {
            $permissions = $role->permissions()->pluck('name')->all();

            $clone = new Role;
            $clone->name = $data['name'];
            $clone->guard_name = $role->guard_name;
            $clone->is_system = false;
            $clone->description = $data['description'] ?? $role->description;
            $clone->save();

            $clone->syncPermissions($permissions);

            AuditLogger::record('role.duplicated', $clone, [], [
                'name' => $clone->name,
                'source_role' => $role->name,
                'permissions' => array_values($permissions),
            ]);

            return $clone;
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', "Rol «{$role->name}» duplicado como «{$clone->name}».");
    }

    public function destroy(DestroyRoleRequest $request, Role $role): RedirectResponse
    {
        $reassignTo = $request->validated()['reassign_to'] ?? null;
        $usersCount = $role->users()->count();

        // Si tiene usuarios asignados, se exige un rol destino para reasignarlos.
        if ($usersCount > 0 && ! $reassignTo) {
            throw ValidationException::withMessages([
                'reassign_to' => 'El rol tiene usuarios asignados. Selecciona un rol destino para reasignarlos.',
            ]);
        }

        $reassignedUserIds = [];

        DB::transaction(function () use ($role, $reassignTo, $usersCount, &$reassignedUserIds) {
            if ($usersCount > 0 && $reassignTo) {
                $targetRole = Role::where('name', $reassignTo)->where('guard_name', 'web')->firstOrFail();

                // Reasignar manteniendo "un rol por usuario".
                foreach ($role->users()->get() as $user) {
                    $user->syncRoles([$targetRole->name]);
                    $reassignedUserIds[] = $user->getKey();
                }
            }

            AuditLogger::record(
                'role.deleted',
                $role,
                [
                    'name' => $role->name,
                    'users_count' => $usersCount,
                ],
                $reassignTo === null ? [] : [
                    'reassigned_to' => $reassignTo,
                    'reassigned_users' => $reassignedUserIds,
                ],
            );

            $role->delete();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $message = $reassignTo === null
            ? 'Rol eliminado correctamente.'
            : "Rol eliminado y {$usersCount} usuario(s) reasignado(s) a «{$reassignTo}».";

        return redirect()->route('roles.index')->with('status', $message);
    }

    // =====================================================================
    //  Helpers
    // =====================================================================

    /**
     * Datos comunes de los formularios de alta/edición: permisos agrupados por
     * módulo (etiqueta de módulo => lista de nombres de permiso).
     *
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'permissionGroups' => $this->permissionGroups(),
        ];
    }

    /**
     * Catálogo completo de permisos agrupado por módulo.
     *
     * @return array<string, array<int, string>>
     */
    protected function permissionGroups(): array
    {
        return Permission::query()
            ->orderBy('name')
            ->pluck('name')
            ->groupBy(fn (string $name) => $this->moduleLabel($name))
            ->map(fn (Collection $names) => $names->values()->all())
            ->all();
    }

    /**
     * Agrupa una lista concreta de nombres de permiso por módulo.
     *
     * @param  array<int, string>  $names
     * @return array<string, array<int, string>>
     */
    protected function groupPermissionNames(array $names): array
    {
        return collect($names)
            ->sort()
            ->groupBy(fn (string $name) => $this->moduleLabel($name))
            ->map(fn (Collection $items) => $items->values()->all())
            ->all();
    }

    private function moduleLabel(string $permissionName): string
    {
        $module = explode('.', $permissionName)[0];

        return self::MODULE_LABELS[$module] ?? ucfirst($module);
    }

    /**
     * Roles a los que se pueden reasignar usuarios al eliminar un rol:
     * todos excepto el propio rol y el rol global SuperAdministrador.
     */
    protected function assignableTargetRoles(Role $role): Collection
    {
        return Role::query()
            ->where('id', '!=', $role->id)
            ->where('name', '!=', $this->superAdminRoleName())
            ->orderBy('name')
            ->get();
    }
}
