<?php

namespace Database\Factories;

use App\Enums\CongregationStatus;
use App\Models\Congregation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Congregation>
 */
class CongregationFactory extends Factory
{
    protected $model = Congregation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Congregación '.fake()->unique()->city(),
            'subdominio' => Str::slug(fake()->unique()->word().fake()->randomNumber(3)),
            'estado' => CongregationStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => CongregationStatus::Inactive,
        ]);
    }
}
