<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrdenCompras;

class OrdenComprasSeeder extends Seeder
{
    public function run()
    {
        OrdenCompras::factory()->count(5)->create();
    }
}
