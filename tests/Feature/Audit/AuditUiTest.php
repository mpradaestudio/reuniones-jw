<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Models\Congregation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Módulo Auditoría — UI (PR B): render del listado Bootstrap 5 (filtros, tabla,
 * badges, estado vacío), pantalla de detalle (IP/user agent + old/new) y entrada
 * de menú según `audit.view`. La autorización sigue gobernada por AuditLogPolicy.
 */
class AuditUiTest extends TestCase
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

    public function test_listing_renders_filters_table_and_event_badge_for_super_admin(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin = $this->makeUser('SuperAdministrador', null);

        AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'event' => 'user.created',
            'auditable_type' => User::class,
            'auditable_id' => 1,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('audit.index'));

        $response->assertOk()
            ->assertViewIs('audit.index')
            // Controles de filtro.
            ->assertSee('Desde')
            ->assertSee('Hasta')
            ->assertSee('Evento')
            ->assertSee('Tipo de entidad')
            ->assertSee('name="autor"', false)
            // El filtro de congregación solo se muestra al SuperAdministrador.
            ->assertSee('name="congregation"', false)
            // Cabeceras de tabla.
            ->assertSee('Fecha y hora')
            ->assertSee('Entidad afectada')
            // Badge del evento.
            ->assertSee('user.created');
    }

    public function test_listing_hides_congregation_filter_for_congregation_admin(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $response = $this->actingAs($admin)->get(route('audit.index'));

        $response->assertOk()
            ->assertSee('Auditoría')
            ->assertDontSee('name="congregation"', false);
    }

    public function test_listing_shows_empty_state(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->get(route('audit.index'))
            ->assertOk()
            ->assertSee('No hay registros de auditoría con los criterios indicados.');
    }

    public function test_detail_renders_metadata_ip_user_agent_and_values(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $log = AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'event' => 'user.updated',
            'auditable_type' => User::class,
            'auditable_id' => 99,
            'ip_address' => '198.51.100.23',
            'user_agent' => 'NavegadorPrueba/9.9',
            'old_values' => ['estado' => 'inactive'],
            'new_values' => ['estado' => 'active'],
        ]);

        $response = $this->actingAs($admin)->get(route('audit.show', $log));

        $response->assertOk()
            ->assertViewIs('audit.show')
            ->assertSee('Dirección IP')
            ->assertSee('198.51.100.23')
            ->assertSee('User-Agent')
            ->assertSee('NavegadorPrueba/9.9')
            ->assertSee('Valores anteriores')
            ->assertSee('Valores nuevos')
            ->assertSee('inactive')
            ->assertSee('active');
    }

    public function test_menu_shows_audit_link_for_authorized_user(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Auditoría')
            ->assertSee(route('audit.index'), false);
    }

    public function test_menu_hides_audit_link_for_unauthorized_user(): void
    {
        $congregation = Congregation::factory()->create();
        $basicUser = $this->makeUser('Usuario', $congregation->id);

        $this->actingAs($basicUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Auditoría');
    }
}
