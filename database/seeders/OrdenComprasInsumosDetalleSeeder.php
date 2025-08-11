<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrdenComprasInsumosDetalle;
use Illuminate\Support\Facades\Log;

class OrdenComprasInsumosDetalleSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Ejecutando OrdenComprasInsumosDetalleSeeder');
        try {
            OrdenComprasInsumosDetalle::factory()->count(20)->create();
            Log::info('OrdenComprasInsumosDetalleSeeder completado');
        } catch (\Exception $e) {
            Log::error('Error en OrdenComprasInsumosDetalleSeeder: ' . $e->getMessage());
            throw $e;
        }
    }
}