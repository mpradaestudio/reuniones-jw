<?php

namespace Tests\Feature\Users;

use App\Enums\UserStatus;
use App\Models\Congregation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Reglas de negocio del módulo Usuarios (decisiones aprobadas):
 *  - Email único global.
 *  - Un rol por usuario.
 *  - El último AdministradorCongregación activo no puede desactivarse.
 *  - Un usuario inactivo no puede iniciar sesión.
 *  - Un usuario no puede cambiar su propio estado.
 */
class UserManagementRulesTest extends TestCase
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

    public function test_email_must_be_unique_globally_across_congregations(): void
    {
        $congregationA = Congregation::factory()->create();
        $congregationB = Congregation::factory()->create();

        $existing = $this->makeUser('Usuario', $congregationA->id);
        $admin = $this->makeUser('AdministradorCongregacion', $congregationB->id);

        $this->from(route('users.index'))
            ->actingAs($admin)
            ->post(route('users.store'), $this->validPayload(['email' => $existing->email]))
            ->assertSessionHasErrors('email');
    }

    public function test_exactly_one_role_is_assigned_on_create(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->post(route('users.store'), $this->validPayload([
                'email' => 'unrol@ejemplo.test',
                'role' => 'Usuario',
            ]))
            ->assertRedirect(route('users.index'));

        $created = User::where('email', 'unrol@ejemplo.test')->first();
        $this->assertCount(1, $created->roles);
        $this->assertTrue($created->hasRole('Usuario'));
    }

    public function test_updating_role_replaces_the_previous_one(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin = $this->makeUser('SuperAdministrador', null);
        $target = $this->makeUser('Usuario', $congregation->id);

        $this->actingAs($superAdmin)
            ->put(route('users.update', $target), $this->validPayload([
                'email' => $target->email,
                'role' => 'AdministradorCongregacion',
            ]))
            ->assertRedirect(route('users.index'));

        $target->refresh();
        $this->assertCount(1, $target->roles);
        $this->assertTrue($target->hasRole('AdministradorCongregacion'));
        $this->assertFalse($target->hasRole('Usuario'));
    }

    public function test_non_super_admin_cannot_assign_super_admin_role(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->from(route('users.index'))
            ->actingAs($admin)
            ->post(route('users.store'), $this->validPayload([
                'email' => 'escalada@ejemplo.test',
                'role' => 'SuperAdministrador',
            ]))
            ->assertSessionHasErrors('role');
    }

    public function test_last_active_congregation_admin_cannot_be_deactivated(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin = $this->makeUser('SuperAdministrador', null);
        $soleAdmin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->from(route('users.index'))
            ->actingAs($superAdmin)
            ->patch(route('users.toggle-status', $soleAdmin))
            ->assertSessionHasErrors('estado');

        $this->assertSame(UserStatus::Active, $soleAdmin->fresh()->estado);
    }

    public function test_congregation_admin_can_be_deactivated_when_another_admin_exists(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin = $this->makeUser('SuperAdministrador', null);
        $adminOne = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $this->makeUser('AdministradorCongregacion', $congregation->id); // segundo admin

        $this->actingAs($superAdmin)
            ->patch(route('users.toggle-status', $adminOne))
            ->assertRedirect(route('users.index'));

        $this->assertSame(UserStatus::Inactive, $adminOne->fresh()->estado);
    }

    public function test_user_cannot_toggle_their_own_status(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->patch(route('users.toggle-status', $admin))
            ->assertForbidden();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $congregation = Congregation::factory()->create();
        $inactive = User::factory()->create([
            'congregation_id' => $congregation->id,
            'email' => 'inactivo@ejemplo.test',
            'password' => Hash::make('secret-123'),
            'estado' => UserStatus::Inactive,
        ]);
        $inactive->assignRole('Usuario');

        $this->post(route('login'), [
            'email' => 'inactivo@ejemplo.test',
            'password' => 'secret-123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
