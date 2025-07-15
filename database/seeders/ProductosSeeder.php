<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Productos;
use App\Models\ProductoFoto;
use Illuminate\Support\Facades\Log;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        Log::info('🌱 Iniciando ProductosSeeder');

        $totalDeseado = 30;
        $existentes = Productos::count();
        Log::info("🔍 Productos existentes: {$existentes}");

        $faltantes = max(0, $totalDeseado - $existentes);

        if ($faltantes > 0) {
            Log::info("➕ Creando {$faltantes} productos nuevos");

            $productos = Productos::factory()->count($faltantes)->create();

            $productos->each(function ($producto) {
                Log::info("📸 Asignando 2 fotos al producto ID {$producto->id}");

                ProductoFoto::factory()
                    ->count(2)
                    ->create([
                        'producto_id' => $producto->id,
                    ]);
            });
        } else {
            Log::info("✅ Ya existen {$existentes} productos");
        }

        // Verificar y corregir productos sin fotos
        $productosSinFotos = Productos::doesntHave('fotosRelacion')->get();
        Log::info("🔄 Productos sin fotos: {$productosSinFotos->count()}");

        foreach ($productosSinFotos as $producto) {
            Log::info("📸 Agregando 2 fotos al producto existente ID {$producto->id}");

            ProductoFoto::factory()
                ->count(2)
                ->create([
                    'producto_id' => $producto->id,
                ]);
        }

        // Verificar si algún producto tiene más o menos de 2 fotos
        Productos::with('fotosRelacion')->get()->each(function ($producto) {
            $currentCount = $producto->fotosRelacion->count();

            if ($currentCount !== 2) {
                // Eliminar todas y regenerar solo 2
                $producto->fotosRelacion()->delete();
                ProductoFoto::factory()
                    ->count(2)
                    ->create([
                        'producto_id' => $producto->id,
                    ]);

                Log::info("🧹 Corrigiendo fotos para producto ID {$producto->id}");
            }
        });

        Log::info('✅ Finalizó ProductosSeeder');
    }
}
