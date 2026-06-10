<?php

namespace Tests\Feature\Users;

use App\Models\Congregation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Pruebas de autorización del módulo Usuarios:
 *  - Permisos de Spatie a nivel de ruta (middleware `permission:`).
 *  - Aislamiento por congregación (UserPolicy).
 *  - El permiso `users.reset-password` es INDEPENDIENTE de `users.update`.
 */
class UserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeUser(string $role, ?int $congregationId, string $estado = 'active'): User
    {
        $user = User::factory()->create([
            'congregation_id' => $congregationId,
            'estado' => $estado,
        ]);
        $user->assignRole($role);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Nuevo',
            'apellidos' => 'Usuario',
            'email' => 'nuevo.usuario@ejemplo.test',
            'password' => 'secret-123',
            'password_confirmation' => 'secret-123',
            'estado' => 'active',
            'role' => 'Usuario',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->post(route('users.store'), $this->validPayload())
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_create_users(): void
    {
        $congregation = Congregation::factory()->create();
        $actor = $this->makeUser('Usuario', $congregation->id); // solo dashboard.view

        $this->actingAs($actor)
            ->post(route('users.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_congregation_admin_can_create_user_in_own_congregation(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->post(route('users.store'), $this->validPayload(['email' => 'creado@ejemplo.test']))
            ->assertRedirect(route('users.index'));

        $created = User::where('email', 'creado@ejemplo.test')->first();
        $this->assertNotNull($created);
        $this->assertSame($congregation->id, $created->congregation_id);
        $this->assertTrue($created->hasRole('Usuario'));
    }

    public function test_congregation_admin_cannot_update_user_from_another_congregation(): void
    {
        $congregationA = Congregation::factory()->create();
        $congregationB = Congregation::factory()->create();

        $admin = $this->makeUser('AdministradorCongregacion', $congregationA->id);
        $target = $this->makeUser('Usuario', $congregationB->id);

        $this->actingAs($admin)
            ->put(route('users.update', $target), $this->validPayload([
                'email' => $target->email,
            ]))
            ->assertForbidden();
    }

    public function test_super_admin_can_update_user_in_any_congregation(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin = $this->makeUser('SuperAdministrador', null);
        $target = $this->makeUser('Usuario', $congregation->id);

        $this->actingAs($superAdmin)
            ->put(route('users.update', $target), $this->validPayload([
                'nombre' => 'Editado',
                'email' => $target->email,
                'role' => 'Usuario',
            ]))
            ->assertRedirect(route('users.index'));

        $this->assertSame('Editado', $target->fresh()->nombre);
    }

    public function test_reset_password_is_independent_from_update_permission(): void
    {
        $congregation = Congregation::factory()->create();

        // Usuario con permisos de gestión EXCEPTO users.reset-password.
        $actor = User::factory()->create(['congregation_id' => $congregation->id]);
        $actor->givePermissionTo('users.view', 'users.create', 'users.update', 'users.toggle-status');

        $target = $this->makeUser('Usuario', $congregation->id);

        // Puede actualizar...
        $this->actingAs($actor)
            ->put(route('users.update', $target), $this->validPayload(['email' => $target->email]))
            ->assertRedirect(route('users.index'));

        // ...pero NO puede restablecer la contraseña (permiso independiente).
        $this->actingAs($actor)
            ->patch(route('users.reset-password', $target), [
                'password' => 'nueva-clave-123',
                'password_confirmation' => 'nueva-clave-123',
            ])
            ->assertForbidden();
    }

    public function test_user_with_reset_permission_can_reset_password(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $target = $this->makeUser('Usuario', $congregation->id);
        $originalHash = $target->password;

        $this->actingAs($admin)
            ->patch(route('users.reset-password', $target), [
                'password' => 'nueva-clave-123',
                'password_confirmation' => 'nueva-clave-123',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertNotSame($originalHash, $target->fresh()->password);
    }
}
