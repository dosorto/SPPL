<?php

namespace Database\Seeders;

use App\Models\CategoriaProducto;
use Illuminate\Database\Seeder;

class CategoriaProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = ['Producto', 'Materia Prima', 'Insumo', 'Equipo'];
        $userId = \App\Models\User::inRandomOrder()->first()?->id ?? null;

        foreach ($categorias as $nombre) {
            CategoriaProducto::firstOrCreate(
                ['nombre' => $nombre],
                ['created_by' => $userId, 'updated_by' => $userId]
            );
        }
    }
}