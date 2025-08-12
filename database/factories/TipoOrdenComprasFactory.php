<?php

namespace Database\Factories;

use App\Models\TipoOrdenCompras;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoOrdenComprasFactory extends Factory
{
    protected $model = TipoOrdenCompras::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->unique()->word(),
            'empresa_id' => 1, // Ajusta según el ID de empresa disponible
            'created_by' => 1, // Ajusta según usuarios disponibles
            'updated_by' => 1,
            'deleted_by' => null,
        ];
    }
}