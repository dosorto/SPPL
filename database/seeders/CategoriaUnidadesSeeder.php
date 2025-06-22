<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaUnidades;

class CategoriaUnidadSeeder extends Seeder
{
    public function run(): void
    {
        CategoriaUnidad::factory()->count(5)->create();
    }
}
   