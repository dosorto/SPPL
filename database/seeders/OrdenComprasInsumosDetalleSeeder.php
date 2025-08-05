<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrdenComprasInsumosDetalle;
use App\Models\Productos;


class OrdenComprasInsumosDetalleSeeder extends Seeder
{
    public function run(): void
    {
        OrdenComprasInsumosDetalle::factory()->count(20)->create();
    }
}