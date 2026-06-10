<?php

namespace Tests\Feature;

use App\Models\Congregation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CongregationManagementTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'http://reuniones-jw.local';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function superAdmin(): User
    {
        return User::where('email', 'superadmin@reuniones-jw.local')->first();
    }

    private function congregationAdmin(): User
    {
        return User::where('email', 'admin.central@reuniones-jw.local')->first();
    }

    public function test_super_admin_can_see_the_listing(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(self::HOST.'/congregaciones')
            ->assertOk()
            ->assertSee('Congregaciones')
            ->assertSee('central');
    }

    public function test_non_super_admin_cannot_access_congregations(): void
    {
        $this->actingAs($this->congregationAdmin())
            ->get('http://central.reuniones-jw.local/congregaciones')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(self::HOST.'/congregaciones')
            ->assertRedirect(route('login'));
    }

    public function test_super_admin_can_create_a_congregation(): void
    {
        $response = $this->actingAs($this->superAdmin())
            ->post(self::HOST.'/congregaciones', [
                'nombre' => 'Congregación Sur',
                'subdominio' => 'SUR',  // se normaliza a minúsculas
                'estado' => 'active',
            ]);

        $response->assertRedirect(route('congregations.index'));
        $this->assertDatabaseHas('congregations', [
            'nombre' => 'Congregación Sur',
            'subdominio' => 'sur',
            'estado' => 'active',
        ]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'created']);
    }

    public function test_subdomain_must_be_unique(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(self::HOST.'/congregaciones', [
                'nombre' => 'Duplicada',
                'subdominio' => 'central', // ya existe (seeder)
                'estado' => 'active',
            ])
            ->assertSessionHasErrors('subdominio');
    }

    public function test_subdomain_format_is_validated(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(self::HOST.'/congregaciones', [
                'nombre' => 'Inválida',
                'subdominio' => 'Con Espacios!',
                'estado' => 'active',
            ])
            ->assertSessionHasErrors('subdominio');
    }

    public function test_super_admin_can_update_a_congregation(): void
    {
        $congregation = Congregation::where('subdominio', 'central')->first();

        $this->actingAs($this->superAdmin())
            ->put(self::HOST."/congregaciones/{$congregation->id}", [
                'nombre' => 'Congregación Central Renombrada',
                'subdominio' => 'central',
                'estado' => 'active',
            ])
            ->assertRedirect(route('congregations.index'));

        $this->assertDatabaseHas('congregations', [
            'id' => $congregation->id,
            'nombre' => 'Congregación Central Renombrada',
        ]);
    }

    public function test_super_admin_can_change_status_to_suspended(): void
    {
        $congregation = Congregation::where('subdominio', 'norte')->first();

        $this->actingAs($this->superAdmin())
            ->patch(self::HOST."/congregaciones/{$congregation->id}/estado", [
                'estado' => 'suspended',
            ])
            ->assertRedirect(route('congregations.index'));

        $this->assertDatabaseHas('congregations', [
            'id' => $congregation->id,
            'estado' => 'suspended',
        ]);
    }

    public function test_destroy_soft_deletes_the_congregation(): void
    {
        $congregation = Congregation::where('subdominio', 'norte')->first();

        $this->actingAs($this->superAdmin())
            ->delete(self::HOST."/congregaciones/{$congregation->id}")
            ->assertRedirect(route('congregations.index'));

        $this->assertSoftDeleted('congregations', ['id' => $congregation->id]);
    }

    public function test_archived_congregation_can_be_restored(): void
    {
        $congregation = Congregation::where('subdominio', 'norte')->first();
        $congregation->delete();

        $this->actingAs($this->superAdmin())
            ->patch(self::HOST."/congregaciones/{$congregation->id}/restaurar")
            ->assertRedirect(route('congregations.index', ['archivadas' => 1]));

        $this->assertDatabaseHas('congregations', [
            'id' => $congregation->id,
            'deleted_at' => null,
        ]);
    }
}
