<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // cambio jessuri: Inserta personas de ejemplo para poder asociarlas a empleados.
        \App\Models\Persona::create([
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
            'primer_apellido' => 'Pérez',
            'segundo_apellido' => 'Gómez',
            'dni' => '0801199000011',
            'direccion' => 'Colonia Centro',
            'municipio_id' => 1,
            'pais_id' => 80,
            'telefono' => '9999-8888',
            'sexo' => 'MASCULINO',
            'fecha_nacimiento' => '1990-01-01',
            'fotografia' => null,
        ]);
        \App\Models\Persona::create([
            'primer_nombre' => 'Ana',
            'segundo_nombre' => 'Lucía',
            'primer_apellido' => 'Martínez',
            'segundo_apellido' => 'Ramírez',
            'dni' => '0801199000022',
            'direccion' => 'Barrio Abajo',
            'municipio_id' => 2,
            'pais_id' => 80,
            'telefono' => '8888-7777',
            'sexo' => 'FEMENINO',
            'fecha_nacimiento' => '1992-05-10',
            'fotografia' => null,
        ]);
    }
}
