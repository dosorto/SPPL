<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CategoriaUnidad;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoriaUnidad>
 */
class CategoriaUnidadFactory extends Factory
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
        ];
    }
}
