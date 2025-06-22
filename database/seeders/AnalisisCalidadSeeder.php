<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnalisisCalidad;

class AnalisisCalidadSeeder extends Seeder
{
    public function run(): void
    {
        AnalisisCalidad::factory()->count(15)->create();
    }
}
