<?php

namespace Tests\Feature\Publishers;

use App\Enums\PublisherPrivilege;
use App\Enums\PublisherStatus;
use App\Models\Congregation;
use App\Models\Publisher;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Autorización del módulo Publicadores:
 *  - Rutas protegidas por permiso de Spatie.
 *  - Aislamiento por congregación (PublisherPolicy).
 *  - publishers.delete solo para SuperAdministrador (decisión C).
 *  - Protección del último anciano activo (decisión D).
 */
class PublisherAuthorizationTest extends TestCase
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
            'estado'          => 'active',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function makePublisher(int $congregationId, array $attributes = []): Publisher
    {
        return Publisher::factory()->create(array_merge(
            ['congregation_id' => $congregationId],
            $attributes,
        ));
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nombre'     => 'Juan',
            'apellidos'  => 'Pérez',
            'genero'     => 'masculino',
            'estado'     => PublisherStatus::Active->value,
            'privilegio' => PublisherPrivilege::Publisher->value,
            'es_nombrado' => false,
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // Invitado
    // -----------------------------------------------------------------------

    public function test_guest_is_redirected_to_login_on_store(): void
    {
        $this->post(route('publishers.store'), $this->validPayload())
            ->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // Sin permiso
    // -----------------------------------------------------------------------

    public function test_usuario_sin_permiso_no_puede_crear_publicador(): void
    {
        $congregation = Congregation::factory()->create();
        $actor = $this->makeUser('Usuario', $congregation->id); // sin publishers.create

        $this->actingAs($actor)
            ->post(route('publishers.store'), $this->validPayload())
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // AdministradorCongregacion — puede crear en su congregación
    // -----------------------------------------------------------------------

    public function test_admin_puede_crear_publicador_en_su_congregacion(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $response = $this->actingAs($admin)
            ->post(route('publishers.store'), $this->validPayload());

        $response->assertRedirect(route('publishers.index'));
        $this->assertDatabaseHas('publishers', [
            'congregation_id' => $congregation->id,
            'nombre'          => 'Juan',
            'apellidos'       => 'Pérez',
        ]);
    }

    // -----------------------------------------------------------------------
    // Aislamiento por congregación
    // -----------------------------------------------------------------------

    public function test_admin_no_puede_editar_publicador_de_otra_congregacion(): void
    {
        $congA = Congregation::factory()->create();
        $congB = Congregation::factory()->create();

        $admin     = $this->makeUser('AdministradorCongregacion', $congA->id);
        $publisher = $this->makePublisher($congB->id);

        // El CongregationScope (Global Scope de Publisher) oculta registros de
        // otras congregaciones al hacer el route model binding. El resultado es
        // 404 (el recurso no existe en el scope del actor), no 403.
        // Esto es deliberado: la primera línea de defensa es el scope, que
        // impide siquiera saber que el publicador existe. La Policy es la
        // segunda línea para recursos dentro del propio tenant.
        // Contrasta con User (que no tiene Global Scope): allí la Policy devuelve 403.
        $this->actingAs($admin)
            ->put(route('publishers.update', $publisher), $this->validPayload())
            ->assertNotFound();
    }

    public function test_admin_puede_editar_publicador_de_su_congregacion(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $publisher    = $this->makePublisher($congregation->id);

        $this->actingAs($admin)
            ->put(route('publishers.update', $publisher), $this->validPayload(['nombre' => 'Editado']))
            ->assertRedirect(route('publishers.index'));

        $this->assertSame('Editado', $publisher->fresh()->nombre);
    }

    // -----------------------------------------------------------------------
    // publishers.delete solo SuperAdministrador (decisión C)
    // -----------------------------------------------------------------------

    public function test_admin_congregacion_no_puede_eliminar_publicador(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $publisher    = $this->makePublisher($congregation->id);

        $this->actingAs($admin)
            ->delete(route('publishers.destroy', $publisher))
            ->assertForbidden();
    }

    public function test_super_admin_puede_eliminar_publicador(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin   = $this->makeUser('SuperAdministrador', null);

        // Necesita un segundo anciano para que el primero sea eliminable.
        $publisher = $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Publisher->value,
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('publishers.destroy', $publisher))
            ->assertRedirect(route('publishers.index'));

        $this->assertDatabaseMissing('publishers', ['id' => $publisher->id]);
    }

    // -----------------------------------------------------------------------
    // SuperAdministrador opera en cualquier congregación
    // -----------------------------------------------------------------------

    public function test_super_admin_puede_editar_publicador_de_cualquier_congregacion(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin   = $this->makeUser('SuperAdministrador', null);
        $publisher    = $this->makePublisher($congregation->id);

        $this->actingAs($superAdmin)
            ->put(route('publishers.update', $publisher), $this->validPayload(['nombre' => 'Global']))
            ->assertRedirect(route('publishers.index'));

        $this->assertSame('Global', $publisher->fresh()->nombre);
    }

    // -----------------------------------------------------------------------
    // Protección del último anciano activo (decisión D)
    // -----------------------------------------------------------------------

    public function test_no_se_puede_desactivar_al_ultimo_anciano_activo(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        // Un único anciano activo en la congregación.
        $elder = $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Elder->value,
            'estado'     => PublisherStatus::Active->value,
            'genero'     => 'masculino',
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('publishers.toggle-status', $elder), [
                'estado' => PublisherStatus::Inactive->value,
            ]);

        $response->assertSessionHasErrors('privilegio');
        $this->assertSame(PublisherStatus::Active, $elder->fresh()->estado);
    }

    public function test_se_puede_desactivar_anciano_si_hay_otro_activo(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        // Dos ancianos activos.
        $elder1 = $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Elder->value,
            'estado'     => PublisherStatus::Active->value,
            'genero'     => 'masculino',
        ]);
        $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Elder->value,
            'estado'     => PublisherStatus::Active->value,
            'genero'     => 'masculino',
        ]);

        $this->actingAs($admin)
            ->patch(route('publishers.toggle-status', $elder1), [
                'estado' => PublisherStatus::Inactive->value,
            ])
            ->assertRedirect(route('publishers.index'));

        $this->assertSame(PublisherStatus::Inactive, $elder1->fresh()->estado);
    }

    public function test_no_se_puede_degradar_al_ultimo_anciano_activo_en_update(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin   = $this->makeUser('SuperAdministrador', null);

        $elder = $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Elder->value,
            'estado'     => PublisherStatus::Active->value,
            'genero'     => 'masculino',
        ]);

        // Intentar bajar de privilegio al único anciano.
        $response = $this->actingAs($superAdmin)
            ->put(route('publishers.update', $elder), $this->validPayload([
                'genero'     => 'masculino',
                'privilegio' => PublisherPrivilege::Publisher->value,
            ]));

        $response->assertSessionHasErrors('privilegio');
        $this->assertSame(PublisherPrivilege::Elder, $elder->fresh()->privilegio);
    }

    public function test_no_se_puede_eliminar_al_ultimo_anciano_activo(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin   = $this->makeUser('SuperAdministrador', null);

        $elder = $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Elder->value,
            'estado'     => PublisherStatus::Active->value,
            'genero'     => 'masculino',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('publishers.destroy', $elder))
            ->assertSessionHasErrors('privilegio');

        $this->assertDatabaseHas('publishers', ['id' => $elder->id]);
    }
}
