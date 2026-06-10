<?php

namespace Tests\Feature\Roles;

use App\Models\AuditLog;
use App\Models\Congregation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Módulo Roles y Permisos (backend): autorización, gestión de roles,
 * protección de roles de sistema, duplicado, eliminación con reasignación
 * y auditoría.
 */
class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeUser(string $role, ?int $congregationId): User
    {
        $user = User::factory()->create([
            'congregation_id' => $congregationId,
            'estado' => 'active',
        ]);
        $user->syncRoles([$role]);

        return $user;
    }

    private function customRole(string $name, array $permissions = []): Role
    {
        $role = Role::findOrCreate($name, 'web');
        $role->is_system = false;
        $role->description = 'Rol personalizado de prueba.';
        $role->save();
        $role->syncPermissions($permissions);

        return $role;
    }

    // --- Autorización -------------------------------------------------------

    public function test_congregation_admin_cannot_manage_roles(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->post(route('roles.store'), ['name' => 'Coordinador', 'permissions' => []])
            ->assertForbidden();
    }

    public function test_super_admin_can_create_role_with_permissions(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);

        $this->actingAs($super)
            ->post(route('roles.store'), [
                'name' => 'Coordinador',
                'description' => 'Coordina actividades',
                'permissions' => ['dashboard.view', 'users.view'],
            ])
            ->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'Coordinador')->firstOrFail();
        $this->assertFalse($role->is_system);
        $this->assertEqualsCanonicalizing(
            ['dashboard.view', 'users.view'],
            $role->permissions->pluck('name')->all()
        );

        $log = AuditLog::where('event', 'role.created')->latest('id')->firstOrFail();
        $this->assertSame($super->id, $log->user_id);
        $this->assertSame(Role::class, $log->auditable_type);
        $this->assertContains('users.view', $log->new_values['permissions']);
    }

    public function test_role_name_must_be_unique(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);

        $this->from(route('roles.index'))
            ->actingAs($super)
            ->post(route('roles.store'), ['name' => 'Usuario', 'permissions' => []])
            ->assertSessionHasErrors('name');
    }

    // --- Edición y protección de roles de sistema ---------------------------

    public function test_super_admin_can_update_custom_role_permissions(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);
        $role = $this->customRole('Coordinador', ['dashboard.view']);

        $this->actingAs($super)
            ->put(route('roles.update', $role), [
                'name' => 'Coordinador',
                'permissions' => ['dashboard.view', 'users.view', 'users.create'],
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertEqualsCanonicalizing(
            ['dashboard.view', 'users.view', 'users.create'],
            $role->fresh()->permissions->pluck('name')->all()
        );

        $log = AuditLog::where('event', 'role.updated')->latest('id')->firstOrFail();
        $this->assertArrayHasKey('permissions', $log->new_values);
    }

    public function test_system_role_cannot_be_renamed(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);
        $role = Role::where('name', 'AdministradorCongregacion')->firstOrFail();

        $this->from(route('roles.index'))
            ->actingAs($super)
            ->put(route('roles.update', $role), [
                'name' => 'OtroNombre',
                'permissions' => ['dashboard.view'],
            ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'AdministradorCongregacion']);
    }

    public function test_super_administrator_role_always_keeps_all_permissions(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);
        $role = Role::where('name', 'SuperAdministrador')->firstOrFail();
        $totalPermissions = Permission::count();

        // Intento de vaciar los permisos del SuperAdministrador.
        $this->actingAs($super)
            ->put(route('roles.update', $role), [
                'name' => 'SuperAdministrador',
                'permissions' => [],
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertSame($totalPermissions, $role->fresh()->permissions()->count());
    }

    // --- Duplicado ----------------------------------------------------------

    public function test_super_admin_can_duplicate_a_role(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);
        $role = $this->customRole('Coordinador', ['dashboard.view', 'users.view']);

        $this->actingAs($super)
            ->post(route('roles.duplicate', $role), ['name' => 'Coordinador (copia)'])
            ->assertRedirect(route('roles.index'));

        $clone = Role::where('name', 'Coordinador (copia)')->firstOrFail();
        $this->assertFalse($clone->is_system);
        $this->assertEqualsCanonicalizing(
            ['dashboard.view', 'users.view'],
            $clone->permissions->pluck('name')->all()
        );

        $log = AuditLog::where('event', 'role.duplicated')->latest('id')->firstOrFail();
        $this->assertSame('Coordinador', $log->new_values['source_role']);
    }

    // --- Eliminación --------------------------------------------------------

    public function test_system_role_cannot_be_deleted(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);
        $role = Role::where('name', 'Usuario')->firstOrFail();

        $this->actingAs($super)
            ->delete(route('roles.destroy', $role))
            ->assertForbidden();

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_custom_role_without_users_is_deleted(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);
        $role = $this->customRole('Coordinador', ['dashboard.view']);

        $this->actingAs($super)
            ->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'role.deleted']);
    }

    public function test_deleting_role_with_users_requires_reassignment_target(): void
    {
        $congregation = Congregation::factory()->create();
        $super = $this->makeUser('SuperAdministrador', null);
        $role = $this->customRole('Coordinador', ['dashboard.view']);

        $member = $this->makeUser('Usuario', $congregation->id);
        $member->syncRoles(['Coordinador']);

        $this->from(route('roles.index'))
            ->actingAs($super)
            ->delete(route('roles.destroy', $role))
            ->assertSessionHasErrors('reassign_to');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_deleting_role_with_users_reassigns_then_deletes(): void
    {
        $congregation = Congregation::factory()->create();
        $super = $this->makeUser('SuperAdministrador', null);
        $role = $this->customRole('Coordinador', ['dashboard.view']);

        $member = $this->makeUser('Usuario', $congregation->id);
        $member->syncRoles(['Coordinador']);

        $this->actingAs($super)
            ->delete(route('roles.destroy', $role), ['reassign_to' => 'Usuario'])
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);

        $member->refresh();
        $this->assertCount(1, $member->getRoleNames());
        $this->assertTrue($member->hasRole('Usuario'));

        $log = AuditLog::where('event', 'role.deleted')->latest('id')->firstOrFail();
        $this->assertSame('Usuario', $log->new_values['reassigned_to']);
        $this->assertContains($member->id, $log->new_values['reassigned_users']);
    }
}
