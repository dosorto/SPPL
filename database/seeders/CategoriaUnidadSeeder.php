<?php

namespace Database\Seeders;

use App\Models\UnidadMedida;
use App\Models\CategoriaUnidad;
use Illuminate\Database\Seeder;

class UnidadMedidaSeeder extends Seeder
{
    public function run(): void
    {
        CategoriaUnidad::all()->each(function ($categoria) {
            UnidadMedida::factory(2)->create([
                'categoria_id' => $categoria->id
            ]);
        });
    }
}

