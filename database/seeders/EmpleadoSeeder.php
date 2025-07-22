<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $personas = \App\Models\Persona::take(2)->get();
        Empleado::create([
            'persona_id' => $personas[0]->id ?? 1,
            'fecha_ingreso' => now()->subYears(2),
            'salario' => 25000.00,
            'empresa_id' => 1,
            'departamento_empleado_id' => 1,
            'tipo_empleado_id' => 1,
        ]);
        Empleado::create([
            'persona_id' => $personas[1]->id ?? 2,
            'fecha_ingreso' => now()->subYears(1),
            'salario' => 18000.00,
            'empresa_id' => 2,
            'departamento_empleado_id' => 2,
            'tipo_empleado_id' => 2,
        ]);
    }
}
