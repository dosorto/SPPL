<?php

namespace Database\Seeders;

use App\Models\AnalisisCalidad;
use App\Models\Muestra;
use App\Models\TipoAnalisis;
use Illuminate\Database\Seeder;

class AnalisisCalidadSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = TipoAnalisis::all();

        Muestra::all()->each(function ($muestra) use ($tipos) {
            $tipos->random(2)->each(function ($tipo) use ($muestra) {
                AnalisisCalidad::factory()->create([
                    'muestra_id' => $muestra->id,
                    'tipo_analisis_id' => $tipo->id
                ]);
            });
        });
    }
}

