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
 * Módulo Auditoría — listado (solo lectura): autorización, aislamiento por
 * congregación, filtros, paginación y no exposición de IP/user agent.
 */
class AuditIndexTest extends TestCase
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
        $this->get(route('audit.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_audit_permission_is_forbidden(): void
    {
        $congregation = Congregation::factory()->create();
        $actor = $this->makeUser('Usuario', $congregation->id); // sin audit.view

        $this->actingAs($actor)
            ->get(route('audit.index'))
            ->assertForbidden();
    }

    public function test_congregation_admin_can_view_listing(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'event' => 'user.created',
        ]);

        $this->actingAs($admin)
            ->get(route('audit.index'))
            ->assertOk()
            ->assertViewIs('audit.index');
    }

    public function test_listing_is_isolated_per_congregation(): void
    {
        $congregationA = Congregation::factory()->create();
        $congregationB = Congregation::factory()->create();

        $admin = $this->makeUser('AdministradorCongregacion', $congregationA->id);

        $own = AuditLog::factory()->create([
            'congregation_id' => $congregationA->id,
            'event' => 'role.created',
        ]);
        $foreign = AuditLog::factory()->create([
            'congregation_id' => $congregationB->id,
            'event' => 'role.deleted',
        ]);
        $global = AuditLog::factory()->create([
            'congregation_id' => null,
            'event' => 'congregation.created',
        ]);

        $response = $this->actingAs($admin)->get(route('audit.index'));
        $response->assertOk();

        $ids = $response->viewData('logs')->pluck('id');
        $this->assertTrue($ids->contains($own->id));
        $this->assertFalse($ids->contains($foreign->id));
        // Los eventos globales (sin congregación) quedan reservados al SuperAdministrador.
        $this->assertFalse($ids->contains($global->id));
    }

    public function test_super_admin_sees_all_congregations_and_global_events(): void
    {
        $congregationA = Congregation::factory()->create();
        $congregationB = Congregation::factory()->create();

        $superAdmin = $this->makeUser('SuperAdministrador', null);

        $a = AuditLog::factory()->create(['congregation_id' => $congregationA->id]);
        $b = AuditLog::factory()->create(['congregation_id' => $congregationB->id]);
        $global = AuditLog::factory()->create(['congregation_id' => null]);

        $response = $this->actingAs($superAdmin)->get(route('audit.index'));
        $response->assertOk();

        $ids = $response->viewData('logs')->pluck('id');
        $this->assertTrue($ids->contains($a->id));
        $this->assertTrue($ids->contains($b->id));
        $this->assertTrue($ids->contains($global->id));
    }

    public function test_filter_by_event(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $created = AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'event' => 'user.created',
        ]);
        $deleted = AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'event' => 'role.deleted',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('audit.index', ['event' => 'user.created']));
        $response->assertOk();

        $ids = $response->viewData('logs')->pluck('id');
        $this->assertTrue($ids->contains($created->id));
        $this->assertFalse($ids->contains($deleted->id));
    }

    public function test_filter_by_author(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $other = $this->makeUser('Usuario', $congregation->id, ['email' => 'otro@ejemplo.test']);

        $byAdmin = AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'user_id' => $admin->id,
        ]);
        $byOther = AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'user_id' => $other->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('audit.index', ['autor' => $admin->id]));
        $response->assertOk();

        $ids = $response->viewData('logs')->pluck('id');
        $this->assertTrue($ids->contains($byAdmin->id));
        $this->assertFalse($ids->contains($byOther->id));
    }

    public function test_filter_by_date_range(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $old = AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'created_at' => '2026-01-01 10:00:00',
        ]);
        $recent = AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('audit.index', ['desde' => '2026-05-01', 'hasta' => '2026-06-30']));
        $response->assertOk();

        $ids = $response->viewData('logs')->pluck('id');
        $this->assertTrue($ids->contains($recent->id));
        $this->assertFalse($ids->contains($old->id));
    }

    public function test_super_admin_can_filter_by_congregation(): void
    {
        $congregationA = Congregation::factory()->create();
        $congregationB = Congregation::factory()->create();
        $superAdmin = $this->makeUser('SuperAdministrador', null);

        $a = AuditLog::factory()->create(['congregation_id' => $congregationA->id]);
        $b = AuditLog::factory()->create(['congregation_id' => $congregationB->id]);

        $response = $this->actingAs($superAdmin)
            ->get(route('audit.index', ['congregation' => $congregationA->id]));
        $response->assertOk();

        $ids = $response->viewData('logs')->pluck('id');
        $this->assertTrue($ids->contains($a->id));
        $this->assertFalse($ids->contains($b->id));
    }

    public function test_results_are_paginated(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        AuditLog::factory()->count(25)->create([
            'congregation_id' => $congregation->id,
        ]);

        $response = $this->actingAs($admin)->get(route('audit.index'));
        $response->assertOk();

        $logs = $response->viewData('logs');
        $this->assertSame(20, $logs->perPage());
        $this->assertSame(2, $logs->lastPage());
        $this->assertCount(20, $logs->items());
    }

    public function test_listing_does_not_expose_ip_or_user_agent(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        AuditLog::factory()->create([
            'congregation_id' => $congregation->id,
            'ip_address' => '203.0.113.55',
            'user_agent' => 'AgenteSecretoListado/1.0',
        ]);

        $response = $this->actingAs($admin)->get(route('audit.index'));

        $response->assertOk()
            ->assertDontSee('203.0.113.55')
            ->assertDontSee('AgenteSecretoListado/1.0');
    }
}
