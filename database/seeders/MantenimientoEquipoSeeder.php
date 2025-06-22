<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MantenimientoEquipo;

class MantenimientoEquipoSeeder extends Seeder
{
    public function run(): void
    {
        MantenimientoEquipo::factory()->count(10)->create();
    }
}


