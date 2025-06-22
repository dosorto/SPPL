<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnidadDeMedidas;

class UnidadMedidaSeeder extends Seeder
{
    public function run(): void
    {
        UnidadDeMedidas::factory()->count(10)->create();
    }
}
