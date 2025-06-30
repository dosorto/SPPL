<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Productos;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        Productos::factory()->count(20)->create();
    }
}
