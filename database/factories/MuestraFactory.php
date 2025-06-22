<?php

namespace Database\Factories;

use App\Models\Muestra;
use App\Models\InventarioProducto;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class MuestraFactory extends Factory
{
    protected $model = Muestra::class;

    public function definition(): array
    {
        return [
            'inventario_producto' => InventarioProducto::factory(),
            'nombre_muestra' => $this->faker->word(),
            'cantidad' => $this->faker->randomFloat(2, 1, 10),
            'unidades_id' => UnidadMedida::factory(),
            'temperatura' => $this->faker->randomFloat(1, 3, 10),
            'fecha_muestra' => $this->faker->date(),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
