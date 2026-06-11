<?php

namespace Database\Factories;

use App\Enums\PublisherPrivilege;
use App\Enums\PublisherStatus;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publisher>
 */
class PublisherFactory extends Factory
{
    protected $model = Publisher::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $genero = fake()->randomElement(['masculino', 'femenino']);

        return [
            'congregation_id' => null,
            'user_id'         => null,
            'nombre'          => fake()->firstName($genero === 'masculino' ? 'male' : 'female'),
            'apellidos'       => fake()->lastName(),
            'genero'          => $genero,
            'fecha_bautismo'  => fake()->optional(0.8)->dateTimeBetween('-30 years', 'now')?->format('Y-m-d'),
            'estado'          => PublisherStatus::Active,
            'privilegio'      => PublisherPrivilege::Publisher,
            'es_nombrado'     => false,
        ];
    }

    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'genero' => 'masculino',
            'nombre' => fake()->firstName('male'),
        ]);
    }

    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'genero'    => 'femenino',
            'nombre'    => fake()->firstName('female'),
            'privilegio' => PublisherPrivilege::Publisher,
        ]);
    }

    public function elder(): static
    {
        return $this->state(fn (array $attributes) => [
            'genero'     => 'masculino',
            'nombre'     => fake()->firstName('male'),
            'privilegio' => PublisherPrivilege::Elder,
            'es_nombrado' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => PublisherStatus::Inactive,
        ]);
    }

    public function irregular(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => PublisherStatus::Irregular,
        ]);
    }
}
