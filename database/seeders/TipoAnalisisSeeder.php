<?php

namespace Database\Seeders;

use App\Models\TipoAnalisis;
use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class TipoAnalisisSeeder extends Seeder
{
    public function run(): void
    {
        UnidadMedida::all()->each(function ($unidad) {
            TipoAnalisis::factory(2)->create([
                'unidad_id' => $unidad->id
            ]);
        });
    }
}
