<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Productos;
use App\Models\InventarioProductos;

class InventarioProductosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = Productos::all();

        foreach ($productos as $producto) {
            // Crea el registro de inventario para cada producto
            InventarioProductos::create([
                'producto_id'      => $producto->id,
                'cantidad'         => 0,
                'precio_costo'     => 0,
                'precio_detalle'   => 0,
                'precio_promocion' => 0,
            ]);
        }   
    }
}
