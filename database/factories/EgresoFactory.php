<?php

namespace Database\Factories;

use App\Models\Egreso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Egreso>
 */
class EgresoFactory extends Factory
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
            'subcategoria_id' => null,
            'fecha' => fake()->date('Y-m-d'),
            'descripcion' => fake()->sentence(3),
            // Strings are intentional: monetary values must not be floats.
            'monto' => fake()->randomElement([
                '35.00',
                '60.00',
                '85.00',
                '120.00',
                '180.00',
                '250.00',
                '350.00',
            ]),
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
