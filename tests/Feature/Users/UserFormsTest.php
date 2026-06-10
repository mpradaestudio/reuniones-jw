<?php

namespace Tests\Feature\Users;

use App\Models\Congregation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Formularios de alta y edición de usuarios: autorización y renderizado.
 */
class UserFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeUser(string $role, ?int $congregationId, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'congregation_id' => $congregationId,
            'estado' => 'active',
        ], $attributes));
        $user->assignRole($role);

        return $user;
    }

    public function test_create_form_requires_create_permission(): void
    {
        $congregation = Congregation::factory()->create();
        $actor = $this->makeUser('Usuario', $congregation->id); // sin users.create

        $this->actingAs($actor)
            ->get(route('users.create'))
            ->assertForbidden();
    }

    public function test_congregation_admin_can_open_create_form(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertViewIs('users.create')
            ->assertSee('Nuevo usuario')
            ->assertSee('name="role"', false);
    }

    public function test_edit_form_requires_update_permission_and_same_congregation(): void
    {
        $congregationA = Congregation::factory()->create();
        $congregationB = Congregation::factory()->create();

        $admin = $this->makeUser('AdministradorCongregacion', $congregationA->id);
        $foreign = $this->makeUser('Usuario', $congregationB->id);

        $this->actingAs($admin)
            ->get(route('users.edit', $foreign))
            ->assertForbidden();
    }

    public function test_congregation_admin_can_open_edit_form_for_own_user(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $target = $this->makeUser('Usuario', $congregation->id, [
            'nombre' => 'Marta', 'apellidos' => 'Ruiz', 'email' => 'marta@ejemplo.test',
        ]);

        $this->actingAs($admin)
            ->get(route('users.edit', $target))
            ->assertOk()
            ->assertViewIs('users.edit')
            ->assertSee('marta@ejemplo.test')
            ->assertSee('Guardar cambios');
    }

    public function test_listing_shows_create_button_and_row_actions(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $this->makeUser('Usuario', $congregation->id, ['email' => 'fila@ejemplo.test']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee(route('users.create'))
            ->assertSee('Crear usuario')
            ->assertSee('Desactivar');
    }
}
