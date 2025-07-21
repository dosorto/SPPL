<?php

namespace Database\Seeders;

use App\Models\CategoriaProducto;
use App\Models\SubcategoriaProducto;
use Illuminate\Database\Seeder;

class SubcategoriaProductoSeeder extends Seeder
{
    public function run(): void
    {
        $subcategorias = [
            'Producto' => ['Leches', 'Quesos', 'Yogures', 'Mantequillas', 'Cremas', 'Helados'],
            'Materia Prima' => ['Ingredientes Base', 'Aditivos', 'Condimentos'],
            'Insumo' => ['Envases', 'Etiquetado', 'Limpieza'],
            'Equipo' => ['Maquinaria', 'Equipos'],
        ];

        $empresaId = 1; // Ajustar según el tenant
        $userId = \App\Models\User::inRandomOrder()->first()?->id ?? null;

        foreach ($subcategorias as $categoriaNombre => $subs) {
            $categoria = CategoriaProducto::firstOrCreate(
                ['nombre' => $categoriaNombre, 'empresa_id' => $empresaId],
                ['created_by' => $userId, 'updated_by' => $userId]
            );

            foreach ($subs as $subcategoriaNombre) {
                SubcategoriaProducto::firstOrCreate(
                    ['nombre' => $subcategoriaNombre, 'categoria_id' => $categoria->id, 'empresa_id' => $empresaId],
                    ['created_by' => $userId, 'updated_by' => $userId]
                );
            }
        }
    }
}