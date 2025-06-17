<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Departamento;
use App\Models\Paises; // Asegúrate de importar el modelo Paises

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Encontrar el ID de Honduras
        // Es crucial que 'Honduras' ya exista en tu tabla 'paises' antes de ejecutar este seeder.
        $honduras = Paises::where('nombre_pais', 'Honduras')->first();

        // *** LÍNEA DE DEPURACIÓN CRÍTICA ***
        // Verifica si Honduras fue encontrado. Si no, imprime un error y detiene el seeder.
        if (is_null($honduras)) {
            $this->command->error('Error: ¡No se encontró el país "Honduras"!');
            $this->command->warn('Asegúrate de que PaisesSeeder se haya ejecutado correctamente y haya insertado "Honduras".');
            return; // Detiene la ejecución si Honduras no se encuentra
        } else {
            $this->command->info('País "Honduras" encontrado con ID: ' . $honduras->id);
        }

        // 2. Lista de departamentos de Honduras en orden alfabético
        // Esta lista contiene todos los departamentos de Honduras.
        $departamentosHonduras = [
            'Atlántida',
            'Choluteca',
            'Colón',
            'Comayagua',
            'Copán',
            'Cortés',
            'El Paraíso',
            'Francisco Morazán',
            'Gracias a Dios',
            'Intibucá',
            'Islas de la Bahía',
            'La Paz',
            'Lempira',
            'Ocotepeque',
            'Olancho',
            'Santa Bárbara',
            'Valle',
            'Yoro',
        ];

        // 3. Insertar los departamentos
        // Usamos firstOrCreate para evitar duplicados si el seeder se ejecuta varias veces.
        // Si el departamento con ese nombre y pais_id ya existe, no hace nada; si no, lo crea.
        foreach ($departamentosHonduras as $nombreDepartamento) {
            Departamento::firstOrCreate(
                [
                    'nombre_departamento' => $nombreDepartamento,
                    'pais_id' => $honduras->id,
                ],
                [
                    'created_by' => 1, // Puedes ajustar esto según tu lógica de usuarios
                    'updated_by' => 1, // Puedes ajustar esto según tu lógica de usuarios
                ]
            );
        }

        $this->command->info('Todos los departamentos de Honduras han sido insertados o ya existen.');
    }
}
