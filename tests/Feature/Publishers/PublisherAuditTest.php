<?php

namespace Tests\Feature\Publishers;

use App\Enums\PublisherPrivilege;
use App\Enums\PublisherStatus;
use App\Models\AuditLog;
use App\Models\Congregation;
use App\Models\Publisher;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Auditoría del módulo Publicadores:
 *  - publisher.created registra los campos correctos (sin datos sensibles).
 *  - publisher.updated registra solo los campos modificados.
 *  - publisher.status_changed registra el cambio de estado.
 *  - publisher.deleted registra el evento antes del borrado.
 *  - Las acciones bloqueadas (último anciano) NO generan log.
 */
class PublisherAuditTest extends TestCase
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
            'estado' => 'active',
        ]);
        $user->assignRole('AdministradorCongregacion');

        return $user;
    }

    private function makeSuperAdmin(): User
    {
        $user = User::factory()->create(['congregation_id' => null, 'estado' => 'active']);
        $user->assignRole('SuperAdministrador');

        return $user;
    }

    private function makePublisher(int $congregationId, array $attributes = []): Publisher
    {
        return Publisher::factory()->create(array_merge(
            ['congregation_id' => $congregationId],
            $attributes,
        ));
    }

    public function test_crear_publicador_genera_audit_log(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeAdmin($congregation->id);

        $this->actingAs($admin)->post(route('publishers.store'), [
            'nombre' => 'Laura',
            'apellidos' => 'Martínez',
            'genero' => 'femenino',
            'estado' => PublisherStatus::Active->value,
            'privilegio' => PublisherPrivilege::Publisher->value,
            'es_nombrado' => false,
        ]);

        $log = AuditLog::where('event', 'publisher.created')->first();
        $this->assertNotNull($log);
        $this->assertSame(Publisher::class, $log->auditable_type);
        $this->assertSame($congregation->id, $log->congregation_id);

        $new = $log->new_values;
        $this->assertSame('Laura', $new['nombre']);
        $this->assertSame('Martínez', $new['apellidos']);
        $this->assertArrayNotHasKey('fecha_nacimiento', $new); // excluido del MVP
        $this->assertArrayNotHasKey('notas', $new);            // excluido del MVP
    }

    public function test_editar_publicador_registra_solo_campos_modificados(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeAdmin($congregation->id);
        $publisher = $this->makePublisher($congregation->id, ['nombre' => 'Original']);

        $this->actingAs($admin)->put(route('publishers.update', $publisher), [
            'nombre' => 'Modificado',
            'apellidos' => $publisher->apellidos,
            'genero' => $publisher->genero,
            'estado' => $publisher->estado->value,
            'privilegio' => $publisher->privilegio->value,
            'es_nombrado' => $publisher->es_nombrado,
        ]);

        $log = AuditLog::where('event', 'publisher.updated')->first();
        $this->assertNotNull($log);
        $this->assertArrayHasKey('nombre', $log->old_values);
        $this->assertSame('Original', $log->old_values['nombre']);
        $this->assertSame('Modificado', $log->new_values['nombre']);
        // Solo el campo cambiado; apellidos no debe estar en el diff.
        $this->assertArrayNotHasKey('apellidos', $log->old_values ?? []);
    }

    public function test_toggle_status_genera_publisher_status_changed(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeAdmin($congregation->id);
        $publisher = $this->makePublisher($congregation->id);

        $this->actingAs($admin)->patch(route('publishers.toggle-status', $publisher), [
            'estado' => PublisherStatus::Irregular->value,
        ]);

        $log = AuditLog::where('event', 'publisher.status_changed')->first();
        $this->assertNotNull($log);
        $this->assertSame(PublisherStatus::Active->value, $log->old_values['estado']);
        $this->assertSame(PublisherStatus::Irregular->value, $log->new_values['estado']);
    }

    public function test_eliminar_publicador_genera_publisher_deleted(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin = $this->makeSuperAdmin();

        $publisher = $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Publisher->value,
        ]);
        $publisherId = $publisher->id;

        $this->actingAs($superAdmin)->delete(route('publishers.destroy', $publisher));

        $log = AuditLog::where('event', 'publisher.deleted')->first();
        $this->assertNotNull($log);
        $this->assertSame($publisherId, $log->auditable_id);
        $this->assertArrayHasKey('nombre', $log->old_values);

        $this->assertDatabaseMissing('publishers', ['id' => $publisherId]);
    }

    public function test_accion_bloqueada_ultimo_anciano_no_genera_log(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeAdmin($congregation->id);

        $elder = $this->makePublisher($congregation->id, [
            'privilegio' => PublisherPrivilege::Elder->value,
            'estado' => PublisherStatus::Active->value,
            'genero' => 'masculino',
        ]);

        $this->actingAs($admin)->patch(route('publishers.toggle-status', $elder), [
            'estado' => PublisherStatus::Inactive->value,
        ]);

        $this->assertSame(0, AuditLog::where('event', 'publisher.status_changed')->count());
    }
}
