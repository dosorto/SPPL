<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cai;
use App\Models\Empresa;
use Illuminate\Support\Str;

class CaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
 
        $empresas = Empresa::all();

        if ($empresas->isEmpty()) {
            $this->command->error('❌ No hay empresas registradas.');
            return;
        }

        foreach ($empresas as $empresa) {
            Cai::create([
                'empresa_id'           => $empresa->id,
                'numero_actual'        => 0,
                'rango_inicial'        => 1,
                'rango_final'          => 5000,
                'fecha_limite_emision' => now()->addMonths(6),
                'activo'               => true,
                'cai'                  => Str::upper(Str::uuid()),
                'created_by'           => null,
                'updated_by'           => null,
            ]);
        }

        $this->command->info('✅ Se crearon CAIs activos para todas las empresas.');
    }
}
