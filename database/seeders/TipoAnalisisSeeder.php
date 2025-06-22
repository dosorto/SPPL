<?php

namespace Database\Seeders;

use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;
use App\Models\TipoAnalisis;

class TipoAnalisisSeeder extends Seeder
{
    public function run(): void
    {
        TipoAnalisis::factory()->count(5)->create();
    }
}

