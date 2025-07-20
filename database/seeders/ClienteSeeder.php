<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\Persona;
use Faker\Factory as Faker;
use App\Models\Municipio; 

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        $empresas = Empresa::all();
        $municipios = Municipio::all(); // <-- AÑADIR ESTA LÍNEA

        if ($empresas->isEmpty() || $municipios->isEmpty()) { // <-- MODIFICAR ESTA LÍNEA
            $this->command->error('No hay empresas o municipios para asignar clientes. Ejecuta los seeders de Empresas y Municipios primero.');
            return;
        }

        for ($i = 0; $i < 50; $i++) {
            $empresa = $empresas->random();
            $municipio = $municipios->random(); // <-- AÑADIR ESTA LÍNEA

            // Crear una Persona para el cliente
            $persona = Persona::create([
                'primer_nombre' => $faker->firstName,
                'primer_apellido' => $faker->lastName,
                'dni' => $faker->unique()->numerify('####-').$faker->numberBetween(1960, 2005).$faker->unique()->numerify('-#####'),
                'direccion' => $faker->address,
                'telefono' => $faker->phoneNumber,
                'sexo' => $faker->randomElement(['Masculino', 'Femenino']),
                'fecha_nacimiento' => $faker->dateTimeBetween('-70 years', '-18 years'),
                'empresa_id' => $empresa->id,
                'municipio_id' => $municipio->id, // <-- AÑADIR ESTA LÍNEA
                'pais_id' => $municipio->departamento->pais_id, // Asumimos que podemos obtener el país desde el municipio
            ]);

            // Crear el Cliente asociado a la Persona
            Cliente::create([
                'persona_id' => $persona->id,
                'empresa_id' => $empresa->id,
                'rtn' => $faker->optional()->numerify('##############'),
            ]);
        }
        
        $this->command->info('Se han creado 50 clientes de ejemplo con sus personas asociadas.');
    }
}