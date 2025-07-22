<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeduccionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Deducciones::create([
            'deduccion' => 'Seguro Social',
            'valor' => 200.00,
            'tipo_valor' => 'monto',
            'empresa_id' => 1, // Cambia el ID según corresponda
        ]);
        \App\Models\Deducciones::create([
            'deduccion' => 'ISR',
            'valor' => 10.00,
            'tipo_valor' => 'porcentaje',
            'empresa_id' => 1, // Cambia el ID según corresponda
        ]);
        \App\Models\Deducciones::create([
            'deduccion' => 'Cooperativa',
            'valor' => 150.00,
            'tipo_valor' => 'monto',
            'empresa_id' => 1, // Cambia el ID según corresponda
        ]);
    }
}
