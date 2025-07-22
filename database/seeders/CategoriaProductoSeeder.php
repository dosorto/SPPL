<?php

namespace Database\Seeders;

use App\Models\CategoriaProducto;
use Illuminate\Database\Seeder;

class CategoriaProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = ['Producto', 'Materia Prima', 'Insumo', 'Equipo'];
        $empresaId = 1; // Ajustar según el tenant

        foreach ($categorias as $nombre) {
            CategoriaProducto::firstOrCreate(
                ['nombre' => $nombre, 'empresa_id' => $empresaId],
                ['created_by' => 1, 'updated_by' => 1]
            );
        }
    }
}