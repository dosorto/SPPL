<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrdenCompras;

class OrdenComprasSeeder extends Seeder
{
    public function run()
    {
        // Crea 20 registros de OrdenCompras con factory
        OrdenCompras::factory()->count(20)->create();
    }
}
