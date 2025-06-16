<?php

namespace Database\Factories;

use App\Models\TipoAnalisis;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TipoAnalisis>
 */
class TipoAnalisisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_analisis' => $this->faker->word,
            'unidad_id' => UnidadMedida::factory(),
            'rango_min' => 10,
            'rango_max' => 100,
        ];
    }
}
