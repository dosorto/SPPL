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
        $ordenCompras = OrdenCompras::all(); // Usa las 20 órdenes creadas por OrdenComprasSeeder
        $productos = Productos::factory()->count(10)->create();

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