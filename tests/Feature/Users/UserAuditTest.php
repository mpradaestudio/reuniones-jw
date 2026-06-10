<?php

namespace Tests\Feature\Users;

use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\Congregation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Auditoría (`audit_logs`) de las acciones del módulo Usuarios:
 * alta, edición, cambio de estado y restablecimiento de contraseña.
 */
class UserAuditTest extends TestCase
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

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
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

    public function test_creating_a_user_writes_an_audit_log(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->actingAs($admin)
            ->post(route('users.store'), $this->payload(['email' => 'creado@ejemplo.test']));

        $created = User::where('email', 'creado@ejemplo.test')->firstOrFail();
        $log = AuditLog::where('event', 'user.created')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame($congregation->id, $log->congregation_id);
        $this->assertSame(User::class, $log->auditable_type);
        $this->assertSame($created->id, $log->auditable_id);
        $this->assertSame('Usuario', $log->new_values['role']);
        $this->assertSame('creado@ejemplo.test', $log->new_values['email']);
        // La contraseña nunca debe registrarse.
        $this->assertArrayNotHasKey('password', $log->new_values);
    }

    public function test_updating_a_user_logs_only_changed_fields(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $target = $this->makeUser('Usuario', $congregation->id, [
            'nombre' => 'Original', 'apellidos' => 'Apellido', 'email' => 'target@ejemplo.test',
        ]);

        $this->actingAs($admin)->put(route('users.update', $target), $this->payload([
            'nombre' => 'Modificado',
            'apellidos' => 'Apellido',
            'email' => 'target@ejemplo.test',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'Usuario',
        ]));

        $log = AuditLog::where('event', 'user.updated')->latest('id')->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame($target->id, $log->auditable_id);
        // Solo cambió el nombre.
        $this->assertSame('Original', $log->old_values['nombre']);
        $this->assertSame('Modificado', $log->new_values['nombre']);
        $this->assertArrayNotHasKey('apellidos', $log->new_values);
        $this->assertArrayNotHasKey('password', $log->new_values);
    }

    public function test_toggling_status_writes_an_audit_log(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $target = $this->makeUser('Usuario', $congregation->id, ['estado' => UserStatus::Active->value]);

        $this->actingAs($admin)->patch(route('users.toggle-status', $target));

        $log = AuditLog::where('event', 'user.status_changed')->latest('id')->firstOrFail();

        $this->assertSame($target->id, $log->auditable_id);
        $this->assertSame('active', $log->old_values['estado']);
        $this->assertSame('inactive', $log->new_values['estado']);
    }

    public function test_resetting_password_logs_without_storing_the_password(): void
    {
        $congregation = Congregation::factory()->create();
        $admin = $this->makeUser('AdministradorCongregacion', $congregation->id);
        $target = $this->makeUser('Usuario', $congregation->id);

        $this->actingAs($admin)->patch(route('users.reset-password', $target), [
            'password' => 'super-secreta-999',
            'password_confirmation' => 'super-secreta-999',
        ]);

        $log = AuditLog::where('event', 'user.password_reset')->latest('id')->firstOrFail();

        $this->assertSame($target->id, $log->auditable_id);
        $this->assertNull($log->new_values);
        $this->assertNull($log->old_values);
        $this->assertStringNotContainsString('super-secreta-999', (string) json_encode($log->getAttributes()));
    }

    public function test_status_change_blocked_for_last_admin_does_not_write_log(): void
    {
        $congregation = Congregation::factory()->create();
        $superAdmin = $this->makeUser('SuperAdministrador', null);
        $soleAdmin = $this->makeUser('AdministradorCongregacion', $congregation->id);

        $this->from(route('users.index'))
            ->actingAs($superAdmin)
            ->patch(route('users.toggle-status', $soleAdmin))
            ->assertSessionHasErrors('estado');

        $this->assertSame(0, AuditLog::where('event', 'user.status_changed')->count());
    }
}
