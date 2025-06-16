<?php

namespace Database\Seeders;

use App\Models\MantenimientoEquipo;
use Illuminate\Database\Seeder;

class MantenimientoEquipoSeeder extends Seeder
{
    public function run(): void
    {
        MantenimientoEquipo::factory(10)->create();
    }
}

