<?php

namespace Database\Factories;

use App\Models\UnidadMedida;
use App\Models\CategoriaUnidad;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnidadMedidaFactory extends Factory
{
    protected $model = UnidadMedida::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word(),
            'abreviacion' => strtoupper($this->faker->lexify('???')),
            'categoria_id' => CategoriaUnidad::factory(),
            'created_by' => 1,
            'updated_by' => 1,
            'deleted_by' => null,
        ];
    }
}
