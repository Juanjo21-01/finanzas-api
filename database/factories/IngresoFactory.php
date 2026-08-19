<?php

namespace Database\Factories;

use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingreso>
 */
class IngresoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'categoria_id' => null,
            'fecha' => fake()->date('Y-m-d'),
            'fuente' => fake()->randomElement([
                'Trabajo de medio tiempo',
                'Proyecto freelance',
                'Apoyo familiar',
                'Bono ocasional',
            ]),
            // Strings are intentional: monetary values must not be floats.
            'monto' => fake()->randomElement([
                '1800.00',
                '2200.00',
                '2750.00',
                '3200.00',
                '3800.00',
            ]),
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
