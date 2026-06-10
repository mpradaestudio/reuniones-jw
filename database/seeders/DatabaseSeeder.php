<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Orden importante: roles/permisos -> congregaciones -> usuarios.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CongregationSeeder::class,
            UserSeeder::class,
        ]);
    }
}
