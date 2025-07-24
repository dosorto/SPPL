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
        $municipios = Municipio::all(); 

        if ($empresas->isEmpty() || $municipios->isEmpty()) { 
            $this->command->error('No hay empresas o municipios. Ejecuta sus seeders primero.');
            return;
        }

        // --- Lógica para "Consumidor Final" (Tu lógica aquí ya es correcta) ---
        $this->command->info('Asegurando la existencia del cliente "Consumidor Final"...');
        $personaConsumidorFinal = Persona::firstOrCreate(
            ['dni' => '0000000000000'],
            [
                'primer_nombre' => 'Consumidor',
                'primer_apellido' => 'Final',
                'direccion' => 'Ciudad',
                'telefono' => '0000-0000',
                'sexo' => 'MASCULINO',
                'fecha_nacimiento' => now(),
                'municipio_id' => $municipios->first()->id,
                'pais_id' => $municipios->first()->departamento->pais_id,
            ]
        );

        foreach ($empresas as $empresa) {
            Cliente::firstOrCreate(
                [
                    'persona_id' => $personaConsumidorFinal->id,
                    'empresa_id' => $empresa->id,
                ],
                [
                    'rtn' => '00000000000000',
                ]
            );
        }
        $this->command->info('Cliente "Consumidor Final" verificado/creado para ' . $empresas->count() . ' empresas.');


        // --- Creación de Clientes de Ejemplo (Lógica Corregida) ---
        $this->command->info('Creando 50 clientes de ejemplo...');
        for ($i = 0; $i < 50; $i++) {
            $empresa = $empresas->random();
            $municipio = $municipios->random(); 

            // 1. Crear una Persona GLOBAL, sin empresa_id
            $persona = Persona::create([
                'primer_nombre' => $faker->firstName,
                'primer_apellido' => $faker->lastName,
                'dni' => $faker->unique()->numerify('####-').$faker->numberBetween(1960, 2005).$faker->unique()->numerify('-#####'),
                'direccion' => $faker->address,
                'telefono' => $faker->phoneNumber,
                'sexo' => $faker->randomElement(['MASCULINO', 'FEMENINO']),
                'fecha_nacimiento' => $faker->dateTimeBetween('-70 years', '-18 years'),
                'municipio_id' => $municipio->id,
                'pais_id' => $municipio->departamento->pais_id,
                // El campo 'empresa_id' se ha eliminado de aquí, ¡lo cual es correcto!
            ]);

            // 2. Crear el Cliente que ENLAZA la Persona con la Empresa
            Cliente::create([
                'persona_id' => $persona->id,
                'empresa_id' => $empresa->id,
                'rtn' => $faker->optional()->numerify('##############'),
            ]);
        }
        $this->command->info('Se han creado 50 clientes de ejemplo.');
    }
}