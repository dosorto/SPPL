<?php

namespace Database\Factories;

use App\Models\Muestra;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Muestra>
 */
class MuestraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'nombre_muestra' => $this->faker->word,
            'cantidad' => $this->faker->randomFloat(2, 1, 100),
            'unidades_id' => UnidadMedida::factory(),
            'temperatura' => $this->faker->randomFloat(2, 15, 30),
            'fecha_muestra' => $this->faker->date(),
        ];
    }
}
