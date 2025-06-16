<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaUnidad;

class CategoriaUnidadSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = ['Masa', 'Volumen', 'Temperatura', 'Concentración'];

        foreach ($categorias as $nombre) {
            CategoriaUnidad::firstOrCreate(['nombre' => $nombre]);
        }
    }
}

