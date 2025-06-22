<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Muestra;

class MuestraSeeder extends Seeder
{
    public function run(): void
    {
        Muestra::factory()->count(10)->create();
    }
}


