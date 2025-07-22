<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PercepcionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Percepciones::create([
            'percepcion' => 'Bono de Productividad',
            'valor' => 500.00,
        ]);
        \App\Models\Percepciones::create([
            'percepcion' => 'Horas Extras',
            'valor' => 120.50,
        ]);
        \App\Models\Percepciones::create([
            'percepcion' => 'Bonificación Especial',
            'valor' => 250.00,
        ]);
        \App\Models\Percepciones::create([
            'percepcion' => 'Comisión por Ventas',
            'valor' => 300.00,
        ]);
        \App\Models\Percepciones::create([
            'percepcion' => 'Viáticos',
            'valor' => 180.75,
        ]);
        \App\Models\Percepciones::create([
            'percepcion' => 'Aguinaldo',
            'valor' => 1000.00,
        ]);
        \App\Models\Percepciones::create([
            'percepcion' => 'Vacaciones',
            'valor' => 800.00,
        ]);
    }
}
