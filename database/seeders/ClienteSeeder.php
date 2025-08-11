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
        $departamentosValidos = \App\Models\Departamento::pluck('id')->toArray();

        if ($empresas->isEmpty() || $municipios->isEmpty()) { 
            $this->command->error('No hay empresas o municipios. Ejecuta sus seeders primero.');
            return;
        }

          $this->command->info('Asegurando la existencia del cliente "Consumidor Final"...');

        // 1. Busca o crea UNA ÚNICA persona para "Consumidor Final"
        // Se le asigna la primera empresa y municipio solo para cumplir con las restricciones de la tabla,
        // pero esta persona es conceptualmente global.
        $municipioDefault = $municipios->first();
        $empresaDefaultParaPersona = $empresas->first();

        $departamentoIdDefault = $municipioDefault->departamento_id;
        if (!isset($departamentoIdDefault) || !in_array($departamentoIdDefault, $departamentosValidos)) {
            $departamentoIdDefault = null;
        }
        $personaConsumidorFinal = Persona::firstOrCreate(
            ['dni' => '0000000000000'],
            [
                'primer_nombre' => 'Consumidor',
                'primer_apellido' => 'Final',
                'direccion' => 'Ciudad',
                'telefono' => '0000-0000',
                'sexo' => 'MASCULINO',
                'fecha_nacimiento' => now(),
                'empresa_id' => $empresaDefaultParaPersona->id,
                'municipio_id' => $municipioDefault->id,
                'departamento_id' => $departamentoIdDefault,
                'pais_id' => $municipioDefault->departamento->pais_id,
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

        $this->command->info('Asegurando la existencia del cliente "Consumidor Mayorista"...');

        $personaConsumidorMayorista = Persona::firstOrCreate(
            ['dni' => '1111111111111'],
            [
                'primer_nombre' => 'Consumidor',
                'primer_apellido' => 'Mayorista',
                'direccion' => 'Ciudad',
                'telefono' => '0000-0000',
                'sexo' => 'MASCULINO',
                'fecha_nacimiento' => now(),
                'empresa_id' => $empresaDefaultParaPersona->id,
                'municipio_id' => $municipioDefault->id,
                'departamento_id' => $departamentoIdDefault,
                'pais_id' => $municipioDefault->departamento->pais_id,
            ]
        );

        foreach ($empresas as $empresa) {
            // Consumidor Final
            Cliente::firstOrCreate(
                ['persona_id' => $personaConsumidorFinal->id, 'empresa_id' => $empresa->id],
                ['rtn' => '00000000000000']
            );

            // Consumidor Mayorista
            Cliente::firstOrCreate(
                ['persona_id' => $personaConsumidorMayorista->id, 'empresa_id' => $empresa->id],
                ['rtn' => null]
            );
        }
        $this->command->info('Clientes "Consumidor Final" y "Mayorista" verificados/creados para todas las empresas.');


        // --- Creación de Clientes de Ejemplo (Lógica Corregida) ---
        $this->command->info('Creando 50 clientes de ejemplo...');
        for ($i = 0; $i < 50; $i++) {
            $empresa = $empresas->random();
            $municipio = $municipios->random(); 

            $departamentoId = $municipio->departamento_id;
            if (!isset($departamentoId) || !in_array($departamentoId, $departamentosValidos)) {
                $departamentoId = null;
            }
            // Crear una Persona para el cliente
            $persona = Persona::create([
                'primer_nombre' => $faker->firstName,
                'primer_apellido' => $faker->lastName,
                'dni' => $faker->unique()->numerify('####-').$faker->numberBetween(1960, 2005).$faker->unique()->numerify('-#####'),
                'direccion' => $faker->address,
                'telefono' => $faker->phoneNumber,
                'sexo' => $faker->randomElement(['MASCULINO', 'FEMENINO']),
                'fecha_nacimiento' => $faker->dateTimeBetween('-70 years', '-18 years'),
                'empresa_id' => $empresa->id,
                'municipio_id' => $municipio->id,
                'departamento_id' => $departamentoId,
                'pais_id' => $municipio->departamento->pais_id,
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