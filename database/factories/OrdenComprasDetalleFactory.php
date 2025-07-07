<?php

namespace Database\Factories;

use App\Models\OrdenCompras;
use App\Models\Productos;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrdenComprasDetalleFactory extends Factory
{
    public function definition()
    {
        return [
            'orden_compra_id' => OrdenCompras::factory(),
            'producto_id' => Productos::factory(),
            'cantidad' => $this->faker->numberBetween(1, 100),
            'precio' => $this->faker->randomFloat(2, 1, 1000),
            'created_by' => 1, // Asumiendo que existe un usuario con ID 1
            'updated_by' => 1, // Asumiendo que existe un usuario con ID 1
            'deleted_by' => null, // Puede ser null ya que usa soft deletes
        ];
    }
}
