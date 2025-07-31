<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoEmpleado; // Asegúrate de que el nombre del modelo sea correcto

class TipoEmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define los tipos de empleado deseados con sus descripciones
        $tipos = [
            [
                'nombre_tipo' => 'Permanente',
                'descripcion' => 'Empleados con un puesto fijo y permanente en la organización.',
            ],
            [
                'nombre_tipo' => 'Temporales',
                'descripcion' => 'Empleados contratados por un período específico o para un proyecto determinado.',
            ],
        ];

        // Ordenar los tipos alfabéticamente por 'nombre_tipo' si es necesario
        // (En este caso, los he listado intencionalmente en el orden que los mencionaste,
        // pero puedes aplicar 'usort' si quieres un orden alfabético estricto de la lista)
        usort($tipos, function ($a, $b) {
            return strcmp($a['nombre_tipo'], $b['nombre_tipo']);
        });


        // Iterar sobre los tipos y crearlos en la base de datos
        foreach ($tipos as $tipo) {
            TipoEmpleado::firstOrCreate(
                ['nombre_tipo' => $tipo['nombre_tipo']], // Criterio para encontrar o crear
                [
                    'descripcion' => $tipo['descripcion'],
                    'created_by' => 1, // Asume un usuario con ID 1
                    'updated_by' => 1, // Asume un usuario con ID 1
                    'deleted_by' => null, // Asegura que no tenga valor si no ha sido borrado
                ]
            );
            $this->command->info("Tipo de empleado '{$tipo['nombre_tipo']}' insertado o ya existe.");
        }

        $this->command->info('Tipos de empleado "Temporales", "Permanente"  han sido insertados o ya existen.');
    }
}
