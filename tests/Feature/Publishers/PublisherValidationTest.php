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
 * Validaciones de negocio del módulo Publicadores:
 *  - Privilegio anciano/siervo solo para hombres.
 *  - user_id debe pertenecer a la misma congregación.
 *  - user_id no puede vincularse a dos publicadores.
 */
class PublisherValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeAdmin(int $congregationId): User
    {
        $user = User::factory()->create([
            'congregation_id' => $congregationId,
            'estado'          => 'active',
        ]);
        $user->assignRole('AdministradorCongregacion');

        return $user;
    }

    private function base(array $overrides = []): array
    {
        return array_merge([
            'nombre'     => 'Ana',
            'apellidos'  => 'García',
            'genero'     => 'femenino',
            'estado'     => PublisherStatus::Active->value,
            'privilegio' => PublisherPrivilege::Publisher->value,
            'es_nombrado' => false,
        ], $overrides);
    }

    public function test_privilegio_anciano_rechazado_para_mujer(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeAdmin($congregation->id);

        $this->actingAs($admin)
            ->post(route('publishers.store'), $this->base([
                'genero'     => 'femenino',
                'privilegio' => PublisherPrivilege::Elder->value,
            ]))
            ->assertSessionHasErrors('privilegio');
    }

    public function test_privilegio_siervo_ministerial_rechazado_para_mujer(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeAdmin($congregation->id);

        $this->actingAs($admin)
            ->post(route('publishers.store'), $this->base([
                'genero'     => 'femenino',
                'privilegio' => PublisherPrivilege::MinisterialServant->value,
            ]))
            ->assertSessionHasErrors('privilegio');
    }

    public function test_privilegio_publicador_aceptado_para_mujer(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeAdmin($congregation->id);

        $this->actingAs($admin)
            ->post(route('publishers.store'), $this->base([
                'genero'     => 'femenino',
                'privilegio' => PublisherPrivilege::Publisher->value,
            ]))
            ->assertRedirect(route('publishers.index'));
    }

    public function test_user_id_de_otra_congregacion_rechazado(): void
    {
        $congA = Congregation::factory()->create();
        $congB = Congregation::factory()->create();
        $admin = $this->makeAdmin($congA->id);

        // Usuario de otra congregación.
        $foreignUser = User::factory()->create(['congregation_id' => $congB->id]);

        $this->actingAs($admin)
            ->post(route('publishers.store'), $this->base(['user_id' => $foreignUser->id]))
            ->assertSessionHasErrors('user_id');
    }

    public function test_user_id_de_la_misma_congregacion_aceptado(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeAdmin($congregation->id);

        $linkedUser = User::factory()->create([
            'congregation_id' => $congregation->id,
            'estado'          => 'active',
        ]);
        $linkedUser->assignRole('Usuario');

        $this->actingAs($admin)
            ->post(route('publishers.store'), $this->base([
                'nombre'  => 'Carlos',
                'genero'  => 'masculino',
                'user_id' => $linkedUser->id,
            ]))
            ->assertRedirect(route('publishers.index'));

        $this->assertDatabaseHas('publishers', ['user_id' => $linkedUser->id]);
    }

    public function test_user_id_ya_vinculado_a_otro_publicador_rechazado(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeAdmin($congregation->id);

        $linkedUser = User::factory()->create([
            'congregation_id' => $congregation->id,
            'estado'          => 'active',
        ]);
        $linkedUser->assignRole('Usuario');

        // Primer publicador ya vinculado a ese user.
        Publisher::factory()->create([
            'congregation_id' => $congregation->id,
            'user_id'         => $linkedUser->id,
        ]);

        // Intento de crear otro publicador con el mismo user_id.
        $this->actingAs($admin)
            ->post(route('publishers.store'), $this->base([
                'nombre'  => 'Duplicado',
                'genero'  => 'masculino',
                'user_id' => $linkedUser->id,
            ]))
            ->assertSessionHasErrors('user_id');
    }

    public function test_campos_requeridos_fallan_si_ausentes(): void
    {
        $congregation = Congregation::factory()->create();
        $admin        = $this->makeAdmin($congregation->id);

        $this->actingAs($admin)
            ->post(route('publishers.store'), [])
            ->assertSessionHasErrors(['nombre', 'apellidos', 'genero', 'estado', 'privilegio']);
    }
}
