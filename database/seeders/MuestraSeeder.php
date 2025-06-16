<?php

namespace Database\Seeders;

use App\Models\Muestra;
use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class MuestraSeeder extends Seeder
{
    public function run(): void
    {
        UnidadMedida::all()->each(function ($unidad) {
            Muestra::factory(3)->create([
                'unidades_id' => $unidad->id
            ]);
        });
    }
}

