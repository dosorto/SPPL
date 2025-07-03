<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    public function run()
    {
        // Crear 10 empresas usando el factory
        Empresa::factory()->count(10)->create();
    }
}
