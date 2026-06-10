<?php

namespace Database\Seeders;

use App\Enums\CongregationStatus;
use App\Models\Congregation;
use Illuminate\Database\Seeder;

/**
 * Congregaciones de demostración para el entorno local.
 */
class CongregationSeeder extends Seeder
{
    public function run(): void
    {
        Congregation::firstOrCreate(
            ['subdominio' => 'central'],
            ['nombre' => 'Congregación Central', 'estado' => CongregationStatus::Active],
        );

        Congregation::firstOrCreate(
            ['subdominio' => 'norte'],
            ['nombre' => 'Congregación Norte', 'estado' => CongregationStatus::Active],
        );
    }
}
