<?php

namespace Database\Factories;

use App\Models\AnalisisCalidad;
use App\Models\Muestra;
use App\Models\TipoAnalisis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnalisisCalidad>
 */
class AnalisisCalidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'muestra_id' => Muestra::factory(),
            'tipo_analisis_id' => TipoAnalisis::factory(),
            'valor' => $this->faker->randomFloat(2, 10, 100),
            'observaciones' => $this->faker->sentence,
        ];
    }
}
