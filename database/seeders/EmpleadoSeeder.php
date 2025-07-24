<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\Empresa;
use Faker\Factory as Faker;
use App\Models\Persona;
use App\Models\TipoEmpleado;
use App\Models\DepartamentoEmpleado;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // 1. Obtener todas las dependencias necesarias
        $empresas = Empresa::all();
        $tiposEmpleado = TipoEmpleado::all();

        // 2. Validar que existan los registros necesarios para crear empleados
        if ($empresas->isEmpty() || $tiposEmpleado->isEmpty()) {
            $this->command->error('No se pueden crear empleados. Asegúrese de que existan empresas y tipos de empleado. Ejecute los seeders correspondientes primero.');
            return;
        }

        $this->command->info('Creando 50 empleados de ejemplo...');

        // 3. Bucle para crear 50 empleados
        for ($i = 0; $i < 50; $i++) {
            // Seleccionar una empresa al azar
            $empresa = $empresas->random();

            // Obtener los departamentos que pertenecen a la empresa seleccionada
            $departamentosDeLaEmpresa = DepartamentoEmpleado::where('empresa_id', $empresa->id)->get();

            // Si la empresa no tiene departamentos, saltar a la siguiente iteración
            if ($departamentosDeLaEmpresa->isEmpty()) {
                $this->command->warn("La empresa '{$empresa->nombre}' no tiene departamentos, se omitirá la creación de un empleado para ella en esta iteración.");
                continue;
            }

            // Seleccionar un departamento y tipo de empleado al azar
            $departamento = $departamentosDeLaEmpresa->random();
            $tipoEmpleado = $tiposEmpleado->random();

            // 4. Crear una nueva Persona para cada Empleado
            // Esto evita conflictos y asegura que cada empleado tenga datos personales únicos.
            $persona = Persona::create([
                'primer_nombre' => $faker->firstName,
                'primer_apellido' => $faker->lastName,
                'dni' => $faker->unique()->numerify('#############'),
                'direccion' => $faker->address,
                'telefono' => $faker->phoneNumber,
                'sexo' => $faker->randomElement(['MASCULINO', 'FEMENINO']),
                'fecha_nacimiento' => $faker->dateTimeBetween('-60 years', '-18 years'),
                'empresa_id' => $empresa->id,
                'municipio_id' => $empresa->municipio_id,
                'pais_id' => $empresa->pais_id,
            ]);

            // 5. Crear el Empleado asociado a la Persona recién creada
            Empleado::create([
                'numero_empleado' => 'EMP-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'fecha_ingreso' => $faker->dateTimeBetween('-10 years', 'now'),
                'salario' => $faker->randomFloat(2, 12000, 80000),
                'persona_id' => $persona->id,
                'departamento_empleado_id' => $departamento->id,
                'empresa_id' => $empresa->id,
                'tipo_empleado_id' => $tipoEmpleado->id,
            ]);
        }

        $this->command->info('¡Se han creado 50 empleados de ejemplo con éxito!');
    }
}
