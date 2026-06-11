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
 * Módulo Publicadores — listado (PR B):
 *  - Autorización (invitado → login, sin permiso → 403).
 *  - Aislamiento multi-tenant (AdministradorCongregacion solo ve la suya).
 *  - SuperAdministrador ve todas las congregaciones.
 *  - Filtro por estado, privilegio y género.
 *  - Búsqueda libre por nombre / apellidos.
 *  - Paginación (15/pág).
 *  - Menú lateral muestra "Publicadores" según permiso.
 */
class PublisherIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

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

    // -----------------------------------------------------------------------
    // Autorización
    // -----------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('publishers.index'))->assertRedirect(route('login'));
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $congregation = Congregation::factory()->create();
        $actor        = $this->makeUser('Usuario', $congregation->id);

        $this->actingAs($actor)->get(route('publishers.index'))->assertForbidden();
    }

    public function test_admin_congregacion_puede_ver_el_listado(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->get(route('publishers.index'))
            ->assertOk()
            ->assertViewIs('publishers.index');
    }

    // -----------------------------------------------------------------------
    // Aislamiento multi-tenant
    // -----------------------------------------------------------------------

    public function test_admin_solo_ve_publicadores_de_su_congregacion(): void
    {
        $congA = Congregation::factory()->create();
        $congB = Congregation::factory()->create();

        $admin  = $this->makeUser('AdministradorCongregacion', $congA->id);
        $own    = $this->makePublisher($congA->id, ['nombre' => 'Propia', 'apellidos' => 'A']);
        $foreign = $this->makePublisher($congB->id, ['nombre' => 'Ajena', 'apellidos' => 'B']);

        $response = $this->actingAs($admin)->get(route('publishers.index'));
        $ids      = $response->viewData('publishers')->pluck('id');

        $this->assertTrue($ids->contains($own->id));
        $this->assertFalse($ids->contains($foreign->id));
    }

    public function test_super_admin_ve_publicadores_de_todas_las_congregaciones(): void
    {
        $congA = Congregation::factory()->create();
        $congB = Congregation::factory()->create();

        $superAdmin = $this->makeUser('SuperAdministrador', null);
        $pubA       = $this->makePublisher($congA->id);
        $pubB       = $this->makePublisher($congB->id);

        $response = $this->actingAs($superAdmin)->get(route('publishers.index'));
        $ids      = $response->viewData('publishers')->pluck('id');

        $this->assertTrue($ids->contains($pubA->id));
        $this->assertTrue($ids->contains($pubB->id));
    }

    // -----------------------------------------------------------------------
    // Búsqueda
    // -----------------------------------------------------------------------

    public function test_busqueda_por_nombre_filtra_resultados(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $match   = $this->makePublisher($congregation->id, ['nombre' => 'Laura', 'apellidos' => 'Pérez']);
        $noMatch = $this->makePublisher($congregation->id, ['nombre' => 'Carlos', 'apellidos' => 'García']);

        $response = $this->actingAs($admin)->get(route('publishers.index', ['q' => 'Laura']));
        $ids      = $response->viewData('publishers')->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($noMatch->id));
    }

    public function test_busqueda_por_apellidos_filtra_resultados(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $match   = $this->makePublisher($congregation->id, ['nombre' => 'Ana', 'apellidos' => 'Martínez']);
        $noMatch = $this->makePublisher($congregation->id, ['nombre' => 'Pedro', 'apellidos' => 'López']);

        $response = $this->actingAs($admin)->get(route('publishers.index', ['q' => 'Martínez']));
        $ids      = $response->viewData('publishers')->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($noMatch->id));
    }

    // -----------------------------------------------------------------------
    // Filtros
    // -----------------------------------------------------------------------

    public function test_filtro_por_estado(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $activo   = $this->makePublisher($congregation->id, ['estado' => PublisherStatus::Active]);
        $inactivo = $this->makePublisher($congregation->id, ['estado' => PublisherStatus::Inactive]);

        $response = $this->actingAs($admin)->get(route('publishers.index', [
            'estado' => PublisherStatus::Active->value,
        ]));
        $ids = $response->viewData('publishers')->pluck('id');

        $this->assertTrue($ids->contains($activo->id));
        $this->assertFalse($ids->contains($inactivo->id));
    }

    public function test_filtro_por_privilegio(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $elder = $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Elder,
            'genero'     => 'masculino',
        ]);
        $pub = $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Publisher,
        ]);

        $response = $this->actingAs($admin)->get(route('publishers.index', [
            'privilegio' => PublisherPrivilege::Elder->value,
        ]));
        $ids = $response->viewData('publishers')->pluck('id');

        $this->assertTrue($ids->contains($elder->id));
        $this->assertFalse($ids->contains($pub->id));
    }

    public function test_filtro_por_genero(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $male   = $this->makePublisher($congregation->id, ['genero' => 'masculino']);
        $female = $this->makePublisher($congregation->id, ['genero' => 'femenino']);

        $response = $this->actingAs($admin)->get(route('publishers.index', ['genero' => 'femenino']));
        $ids      = $response->viewData('publishers')->pluck('id');

        $this->assertFalse($ids->contains($male->id));
        $this->assertTrue($ids->contains($female->id));
    }

    // -----------------------------------------------------------------------
    // Paginación
    // -----------------------------------------------------------------------

    public function test_paginacion_15_por_pagina(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        Publisher::factory()->count(20)->create(['congregation_id' => $congregation->id]);

        $response    = $this->actingAs($admin)->get(route('publishers.index'));
        $publishers  = $response->viewData('publishers');

        $this->assertSame(15, $publishers->perPage());
        $this->assertSame(2, $publishers->lastPage());
        $this->assertCount(15, $publishers->items());
    }

    // -----------------------------------------------------------------------
    // Menú lateral
    // -----------------------------------------------------------------------

    public function test_menu_muestra_publicadores_para_usuario_con_permiso(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Publicadores')
            ->assertSee(route('publishers.index'), false);
    }

    public function test_menu_oculta_publicadores_para_usuario_sin_permiso(): void
    {
        $congregation = Congregation::factory()->create();
        $basicUser    = $this->makeUser('Usuario', $congregation->id);

        $this->actingAs($basicUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('publishers.index'), false);
    }
}
