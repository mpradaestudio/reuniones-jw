<?php

namespace Tests\Feature\Users;

use App\Enums\UserStatus;
use App\Models\Congregation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Listado de usuarios: autorización, aislamiento por congregación, búsqueda,
 * filtros (estado y rol) y paginación.
 */
class UserIndexTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_view_permission_is_forbidden(): void
    {
        $congregation = Congregation::factory()->create();
        $actor = $this->makeUser('Usuario', $congregation->id); // sin users.view

        $this->actingAs($actor)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_congregation_admin_can_view_listing(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id, [
            'nombre' => 'Ana', 'apellidos' => 'García',
        ]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertViewIs('users.index')
            ->assertSee('Ana García');
    }

    public function test_listing_is_isolated_per_congregation(): void
    {
        $congregationA = Congregation::factory()->create();
        $congregationB = Congregation::factory()->create();

        $admin = $this->makeUser('AdministradorCongregacion', $congregationA->id);
        $own = $this->makeUser('Usuario', $congregationA->id, [
            'nombre' => 'Propio', 'apellidos' => 'Usuario', 'email' => 'propio@ejemplo.test',
        ]);
        $foreign = $this->makeUser('Usuario', $congregationB->id, [
            'nombre' => 'Ajeno', 'apellidos' => 'Usuario', 'email' => 'ajeno@ejemplo.test',
        ]);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk()
            ->assertSee('propio@ejemplo.test')
            ->assertDontSee('ajeno@ejemplo.test');
    }

    public function test_super_admin_sees_users_from_all_congregations(): void
    {
        $congregationA = Congregation::factory()->create();
        $congregationB = Congregation::factory()->create();

        $superAdmin = $this->makeUser('SuperAdministrador', null);
        $this->makeUser('Usuario', $congregationA->id, ['email' => 'a@ejemplo.test']);
        $this->makeUser('Usuario', $congregationB->id, ['email' => 'b@ejemplo.test']);

        $this->actingAs($superAdmin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('a@ejemplo.test')
            ->assertSee('b@ejemplo.test');
    }

    public function test_search_filters_by_name_email_or_surname(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->makeUser('Usuario', $congregation->id, [
            'nombre' => 'Carlos', 'apellidos' => 'Pérez', 'email' => 'carlos@ejemplo.test',
        ]);
        $this->makeUser('Usuario', $congregation->id, [
            'nombre' => 'Lucía', 'apellidos' => 'Gómez', 'email' => 'lucia@ejemplo.test',
        ]);

        $response = $this->actingAs($admin)->get(route('users.index', ['q' => 'carlos']));

        $response->assertOk()
            ->assertSee('carlos@ejemplo.test')
            ->assertDontSee('lucia@ejemplo.test');
    }

    public function test_filter_by_estado(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->makeUser('Usuario', $congregation->id, [
            'email' => 'uno@ejemplo.test', 'estado' => UserStatus::Active->value,
        ]);
        $this->makeUser('Usuario', $congregation->id, [
            'email' => 'dos@ejemplo.test', 'estado' => UserStatus::Inactive->value,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.index', ['estado' => UserStatus::Inactive->value]));

        $response->assertOk()
            ->assertSee('dos@ejemplo.test')
            ->assertDontSee('uno@ejemplo.test');
    }

    public function test_filter_by_role(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id, [
            'email' => 'admin@ejemplo.test',
        ]);
        $this->makeUser('Usuario', $congregation->id, ['email' => 'simple@ejemplo.test']);

        $response = $this->actingAs($admin)
            ->get(route('users.index', ['role' => 'Usuario']));

        $response->assertOk()
            ->assertSee('simple@ejemplo.test')
            ->assertDontSee('admin@ejemplo.test');
    }

    public function test_results_are_paginated(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        // 15 por página: creamos 20 usuarios adicionales -> 2 páginas.
        User::factory()->count(20)->create([
            'congregation_id' => $congregation->id,
            'estado' => 'active',
        ])->each(fn (User $u) => $u->assignRole('Usuario'));

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $users = $response->viewData('users');
        $this->assertSame(15, $users->perPage());
        $this->assertSame(2, $users->lastPage());
        $this->assertCount(15, $users->items());
    }
}
