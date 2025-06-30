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
            'nombre' => $this->faker->unique()->word(),  // nombre único y válido
            'created_by' => 1,   // cambia según usuarios que tengas
            'updated_by' => 1,
            'deleted_by' => null,
        ];
    }
}
