<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaUnidades;

class CategoriaUnidadesSeeder extends Seeder
{
    public function run(): void
    {
        CategoriaUnidades::factory()->count(5)->create();
    }
}
