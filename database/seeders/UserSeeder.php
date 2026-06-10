<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Congregation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuarios iniciales:
 *  - 1 SuperAdministrador global (sin congregación).
 *  - Por cada congregación demo: 1 AdministradorCongregacion y 1 Usuario.
 *
 * Credenciales de demostración (cambiar en producción):
 *  - superadmin@reuniones-jw.local / password
 *  - admin.central@reuniones-jw.local / password
 *  - usuario.central@reuniones-jw.local / password
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // SuperAdministrador global.
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@reuniones-jw.local'],
            [
                'congregation_id' => null,
                'nombre' => 'Super',
                'apellidos' => 'Administrador',
                'password' => Hash::make('password'),
                'estado' => UserStatus::Active,
            ],
        );
        $superAdmin->syncRoles(['SuperAdministrador']);

        // Para cada congregación: un administrador y un usuario.
        Congregation::all()->each(function (Congregation $congregation) {
            $admin = User::firstOrCreate(
                ['email' => "admin.{$congregation->subdominio}@reuniones-jw.local"],
                [
                    'congregation_id' => $congregation->id,
                    'nombre' => 'Administrador',
                    'apellidos' => $congregation->nombre,
                    'password' => Hash::make('password'),
                    'estado' => UserStatus::Active,
                ],
            );
            $admin->syncRoles(['AdministradorCongregacion']);

            $user = User::firstOrCreate(
                ['email' => "usuario.{$congregation->subdominio}@reuniones-jw.local"],
                [
                    'congregation_id' => $congregation->id,
                    'nombre' => 'Usuario',
                    'apellidos' => $congregation->nombre,
                    'password' => Hash::make('password'),
                    'estado' => UserStatus::Active,
                ],
            );
            $user->syncRoles(['Usuario']);
        });
    }
}
