<?php

namespace Database\Seeders;

use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;
use App\Models\Productos;
use Illuminate\Database\Seeder;

class OrdenComprasDetalleSeeder extends Seeder
{
    public function run()
    {
        // Crear 5 órdenes de compra
        $ordenCompras = OrdenCompras::factory()->count(5)->create();
        
        // Crear 10 productos
        $productos = Productos::factory()->count(10)->create();

        // Crear 4 detalles por cada orden de compra
        foreach ($ordenCompras as $ordenCompra) {
            for ($i = 0; $i < 4; $i++) {
                OrdenComprasDetalle::factory()->create([
                    'orden_compra_id' => $ordenCompra->id,
                    'producto_id' => $productos->random()->id,
                ]);
            }
        }
    }
}