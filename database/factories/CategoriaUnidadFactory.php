<?php

namespace Database\Factories;

use App\Models\CategoriaUnidad;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaUnidadFactory extends Factory
{
    protected $model = CategoriaUnidad::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word(),
            'created_by' => 1,
            'updated_by' => 1,
            'deleted_by' => null,
        ];
    }
}
