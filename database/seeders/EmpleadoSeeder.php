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
        Empleado::create([
            'nombre' => 'Juan Pérez',
            'email' => 'juan.perez@empresa.com',
            'telefono' => '9999-8888',
            'empresa_id' => 1,
            'departamento_empleado_id' => 1, // cambio jessuri: corregido el nombre del campo
            'tipo_empleado_id' => 1,
        ]);
        Empleado::create([
            'nombre' => 'Ana López',
            'email' => 'ana.lopez@soluciones.com',
            'telefono' => '7777-6666',
            'empresa_id' => 2,
            'departamento_empleado_id' => 2, // cambio jessuri: corregido el nombre del campo
            'tipo_empleado_id' => 2,
        ]);
    }
}
