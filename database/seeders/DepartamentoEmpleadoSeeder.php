<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartamentoEmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // cambio jessuri: Crea los departamentos solo si no existen para la empresa, evitando duplicados por empresa.
        $departamentos = [
            [
                'nombre_departamento_empleado' => 'Administración',
                'descripcion' => 'Departamento administrativo',
            ],
            [
                'nombre_departamento_empleado' => 'Producción',
                'descripcion' => 'Departamento de producción',
            ],
            [
                'nombre_departamento_empleado' => 'Recursos Humanos',
                'descripcion' => 'Departamento de RRHH',
            ],
        ];
        $empresas = \App\Models\Empresa::all();
        foreach ($empresas as $empresa) {
            foreach ($departamentos as $dep) {
                \App\Models\DepartamentoEmpleado::firstOrCreate([
                    'nombre_departamento_empleado' => $dep['nombre_departamento_empleado'],
                    'empresa_id' => $empresa->id,
                ], [
                    'descripcion' => $dep['descripcion'],
                ]);
            }
        }
    }
}
