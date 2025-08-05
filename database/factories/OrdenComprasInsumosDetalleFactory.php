<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OrdenComprasInsumos;
use App\Models\Productos;

class OrdenComprasInsumosDetalleFactory extends Factory
{
    protected $model = \App\Models\OrdenComprasInsumosDetalle::class;

    public function definition(): array
    {
        $precio_unitario = $this->faker->randomFloat(2, 10, 100);
        $cantidad = $this->faker->numberBetween(1, 50);
        return [
            'orden_compra_insumo_id' => OrdenComprasInsumos::factory(),
            'producto_id' => Productos::factory(),
            'cantidad' => $cantidad,
            'precio_unitario' => $precio_unitario,
            'subtotal' => $precio_unitario * $cantidad,
        ];
    }
}