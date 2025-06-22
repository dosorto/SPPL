<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnidadMedida;

class UnidadMedidaSeeder extends Seeder
{
    public function run(): void
    {
        UnidadMedida::factory()->count(10)->create();
    }
}
