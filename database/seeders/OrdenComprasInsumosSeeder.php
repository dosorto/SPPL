<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrdenComprasInsumos;

class OrdenComprasInsumosSeeder extends Seeder
{
    public function run(): void
    {
        OrdenComprasInsumos::factory()->count(10)->create();
    }
}