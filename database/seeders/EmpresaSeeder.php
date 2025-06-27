<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Empresa::create([
            'nombre' => 'Empresa Ejemplo S.A.',
            'direccion' => 'Calle Principal 123',
            'telefono' => '2222-3333',
            'email' => 'info@empresa.com',
        ]);
        Empresa::create([
            'nombre' => 'Soluciones Globales',
            'direccion' => 'Avenida Central 456',
            'telefono' => '4444-5555',
            'email' => 'contacto@soluciones.com',
        ]);
    }
}
