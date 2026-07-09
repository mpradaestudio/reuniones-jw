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
 * Módulo Auditoría — detalle (solo lectura): autorización, aislamiento por
 * congregación y exposición de IP/user agent únicamente en el detalle.
 */
class AuditShowTest extends TestCase
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

    public function test_user_without_audit_permission_is_forbidden(): void
    {
        $congregation = Congregation::factory()->create();
        $actor = $this->makeUser('Usuario', $congregation->id);
        $log = AuditLog::factory()->create(['congregation_id' => $congregation->id]);

        $this->actingAs($actor)
            ->get(route('audit.show', $log))
            ->assertForbidden();
    }

    public function test_congregation_admin_can_view_own_congregation_log(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $log = AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'event' => 'user.updated',
        ]);

        $this->actingAs($admin)
            ->get(route('audit.show', $log))
            ->assertOk()
            ->assertViewIs('audit.show')
            ->assertSee('user.updated');
    }

    public function test_congregation_admin_cannot_view_foreign_congregation_log(): void
    {
        $congregationA = Congregation::factory()->create();
        $congregationB = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregationA->id);
        $foreignLog = AuditLog::factory()->create(['congregation_id' => $congregationB->id]);

        $this->actingAs($admin)
            ->get(route('audit.show', $foreignLog))
            ->assertForbidden();
    }

    public function test_congregation_admin_cannot_view_global_log(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $globalLog = AuditLog::factory()->create(['congregation_id' => null]);

        $this->actingAs($admin)
            ->get(route('audit.show', $globalLog))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_any_log_including_global(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin = $this->makeUser('SuperAdministrador', null);

        $congregationLog = AuditLog::factory()->create(['congregation_id' => $congregation->id]);
        $globalLog = AuditLog::factory()->create(['congregation_id' => null]);

        $this->actingAs($superAdmin)->get(route('audit.show', $congregationLog))->assertOk();
        $this->actingAs($superAdmin)->get(route('audit.show', $globalLog))->assertOk();
    }

    public function test_detail_exposes_ip_and_user_agent(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $log = AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'ip_address' => '198.51.100.7',
            'user_agent' => 'AgenteDetalle/2.0',
        ]);

        $this->actingAs($admin)
            ->get(route('audit.show', $log))
            ->assertOk()
            ->assertSee('198.51.100.7')
            ->assertSee('AgenteDetalle/2.0');
    }
}
