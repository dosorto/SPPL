<?php

namespace Database\Factories;

use App\Models\UnidadMedida;
use App\Models\CategoriaUnidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnidadMedida>
 */
class UnidadMedidaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word,
            'abreviacion' => strtoupper($this->faker->lexify('??')),
            'categoria_id' => CategoriaUnidad::factory(),
        ];
    }
}
