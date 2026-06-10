<?php

namespace Tests\Feature\Roles;

use App\Models\Congregation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * UI del módulo Roles y Permisos: listado (contadores e indicador), detalle,
 * formularios y asistente de reasignación al eliminar.
 */
class RoleUiTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('roles.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_view_permission_is_forbidden(): void
    {
        $congregation = Congregation::factory()->create();
        $user = $this->makeUser('Usuario', $congregation->id); // sin roles.view

        $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
    }

    public function test_index_shows_counts_type_and_create_button_for_manager(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);
        $this->customRole('Coordinador', ['dashboard.view', 'users.view']);

        $response = $this->actingAs($super)->get(route('roles.index'));

        $response->assertOk()
            ->assertViewIs('roles.index')
            ->assertSee('Crear rol')
            ->assertSee('Coordinador')
            ->assertSee('Sistema')
            ->assertSee('Personalizado');

        $roles = $response->viewData('roles');
        $coordinador = $roles->firstWhere('name', 'Coordinador');
        $this->assertSame(2, $coordinador->permissions_count);
    }

    public function test_congregation_admin_can_view_but_not_manage(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        // Puede ver el listado (roles.view) pero sin botón de gestión...
        $this->actingAs($admin)->get(route('roles.index'))
            ->assertOk()
            ->assertDontSee('Crear rol');

        // ...y no puede abrir el formulario de creación (roles.manage).
        $this->actingAs($admin)->get(route('roles.create'))->assertForbidden();
    }

    public function test_show_displays_permissions_grouped_and_users(): void
    {
        $congregation = Congregation::factory()->create();
        $super = $this->makeUser('SuperAdministrador', null);
        $role = $this->customRole('Coordinador', ['users.view', 'users.create']);
        $member = $this->makeUser('Usuario', $congregation->id);
        $member->syncRoles(['Coordinador']);

        $this->actingAs($super)->get(route('roles.show', $role))
            ->assertOk()
            ->assertViewIs('roles.show')
            ->assertSee('Usuarios')        // etiqueta de módulo
            ->assertSee('users.view')
            ->assertSee($member->email);
    }

    public function test_create_form_renders_permission_checkboxes(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);

        $this->actingAs($super)->get(route('roles.create'))
            ->assertOk()
            ->assertViewIs('roles.create')
            ->assertSee('name="permissions[]"', false)
            ->assertSee('dashboard.view');
    }

    public function test_edit_form_marks_system_role_name_readonly(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);
        $role = Role::where('name', 'AdministradorCongregacion')->firstOrFail();

        $this->actingAs($super)->get(route('roles.edit', $role))
            ->assertOk()
            ->assertViewIs('roles.edit')
            ->assertSee('readonly', false);
    }

    public function test_duplicate_form_renders(): void
    {
        $super = $this->makeUser('SuperAdministrador', null);
        $role = $this->customRole('Coordinador', ['dashboard.view']);

        $this->actingAs($super)->get(route('roles.duplicate-form', $role))
            ->assertOk()
            ->assertViewIs('roles.duplicate')
            ->assertSee('Duplicar rol');
    }

    public function test_delete_wizard_offers_reassignment_targets_excluding_super_admin(): void
    {
        $congregation = Congregation::factory()->create();
        $super = $this->makeUser('SuperAdministrador', null);
        $role = $this->customRole('Coordinador', ['dashboard.view']);
        $member = $this->makeUser('Usuario', $congregation->id);
        $member->syncRoles(['Coordinador']);

        $response = $this->actingAs($super)->get(route('roles.delete-form', $role));

        $response->assertOk()
            ->assertViewIs('roles.delete')
            ->assertSee('Reasignar usuarios a')
            ->assertSee('name="reassign_to"', false);

        $targetNames = $response->viewData('targets')->pluck('name');
        $this->assertContains('Usuario', $targetNames);
        $this->assertNotContains('SuperAdministrador', $targetNames); // sin escalada
        $this->assertNotContains('Coordinador', $targetNames);        // no el propio rol
    }
}
