<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Requests\Users\ResetUserPasswordRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Capa backend del módulo Usuarios.
 *
 * Solo contiene las acciones de escritura (store/update/toggleStatus/
 * resetPassword), que redirigen tras procesar. La construcción de vistas,
 * tablas y formularios queda fuera de alcance en esta capa base.
 *
 * Autorización en profundidad:
 *  - Middleware `permission:` en la ruta (permiso de Spatie).
 *  - UserPolicy (permiso + misma congregación) vía Form Requests / authorize().
 *  - Invariante de negocio: no dejar a una congregación sin un
 *    AdministradorCongregación activo.
 */
class UserController extends Controller
{
    /**
     * Listado de usuarios con búsqueda, filtros y paginación (Bootstrap 5).
     *
     * Aislamiento por congregación: un usuario no global solo ve los usuarios de
     * su propia congregación; el SuperAdministrador ve todos. La autorización se
     * resuelve con la UserPolicy (`viewAny`).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();
        $superAdminRole = config('tenancy.super_admin_role', 'SuperAdministrador');

        $search = trim((string) $request->query('q', ''));
        $estado = (string) $request->query('estado', '');
        $role = (string) $request->query('role', '');

        $query = User::query()->with(['roles', 'congregation']);

        // El SuperAdministrador ve todas las congregaciones; el resto, solo la suya.
        if (! $actor->isSuperAdmin()) {
            $query->where('congregation_id', $actor->congregation_id);
        }

        // Búsqueda por nombre, apellidos o email.
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por estado.
        if (in_array($estado, [UserStatus::Active->value, UserStatus::Inactive->value], true)) {
            $query->where('estado', $estado);
        }

        // Filtro por rol.
        if ($role !== '') {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        $users = $query
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->paginate(15)
            ->withQueryString();

        // Roles disponibles para el filtro (el rol global solo lo ve el SuperAdmin).
        $roles = Role::query()
            ->when(! $actor->isSuperAdmin(), fn ($q) => $q->where('name', '!=', $superAdminRole))
            ->orderBy('name')
            ->pluck('name');

        return view('users.index', [
            'users' => $users,
            'roles' => $roles,
            'statuses' => UserStatus::options(),
            'filters' => [
                'q' => $search,
                'estado' => $estado,
                'role' => $role,
            ],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $actor = $request->user();
        $superAdminRole = config('tenancy.super_admin_role', 'SuperAdministrador');

        // El SuperAdministrador es global (sin congregación). El resto de roles
        // se crean dentro de una congregación: la del actor, salvo que un
        // SuperAdministrador indique explícitamente otra.
        if ($data['role'] === $superAdminRole) {
            $congregationId = null;
        } elseif ($actor->isSuperAdmin()) {
            $congregationId = $data['congregation_id'] ?? null;
        } else {
            $congregationId = $actor->congregation_id;
        }

        $user = new User([
            'congregation_id' => $congregationId,
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'estado' => $data['estado'],
        ]);
        $user->save();

        // Un único rol por usuario.
        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $newRoleIsAdmin = $data['role'] === 'AdministradorCongregacion';
        $newStatusIsActive = $data['estado'] === UserStatus::Active->value;

        // Si el usuario es actualmente AdministradorCongregación activo y el cambio
        // lo degradaría (otro rol) o lo desactivaría, proteger al último admin.
        if ($this->isActiveCongregationAdmin($user) && (! $newRoleIsAdmin || ! $newStatusIsActive)) {
            $this->ensureNotLastActiveCongregationAdmin($user, 'role');
        }

        $user->fill([
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'email' => $data['email'],
            'estado' => $data['estado'],
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario actualizado correctamente.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('toggleStatus', $user);

        $deactivating = $user->estado === UserStatus::Active;

        // No dejar a la congregación sin un AdministradorCongregación activo.
        if ($deactivating && $this->isActiveCongregationAdmin($user)) {
            $this->ensureNotLastActiveCongregationAdmin($user, 'estado');
        }

        $user->estado = $deactivating ? UserStatus::Inactive : UserStatus::Active;
        $user->save();

        return redirect()
            ->route('users.index')
            ->with('status', 'Estado del usuario actualizado correctamente.');
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $user->password = Hash::make($request->validated()['password']);
        $user->save();

        return redirect()
            ->route('users.index')
            ->with('status', 'Contraseña restablecida correctamente.');
    }

    /**
     * ¿El usuario es un AdministradorCongregación activo de una congregación?
     */
    protected function isActiveCongregationAdmin(User $user): bool
    {
        return $user->congregation_id !== null
            && $user->estado === UserStatus::Active
            && $user->hasRole('AdministradorCongregacion');
    }

    /**
     * Garantiza que exista al menos otro AdministradorCongregación activo en la
     * misma congregación; de lo contrario, bloquea la operación.
     *
     * @throws ValidationException
     */
    protected function ensureNotLastActiveCongregationAdmin(User $user, string $field): void
    {
        $otherActiveAdmins = User::query()
            ->where('congregation_id', $user->congregation_id)
            ->where('id', '!=', $user->id)
            ->where('estado', UserStatus::Active->value)
            ->whereHas('roles', fn ($query) => $query->where('name', 'AdministradorCongregacion'))
            ->count();

        if ($otherActiveAdmins === 0) {
            throw ValidationException::withMessages([
                $field => 'No se puede dejar a la congregación sin un AdministradorCongregación activo.',
            ]);
        }
    }
}
