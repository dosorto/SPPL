<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use App\Models\Empresa;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        Cliente::factory()->count(15)->create(); // Crea 15 registros de ejemplo
    }
}