<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use Carbon\Carbon;

class FacturaSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $empresas = Empresa::all();
        if ($clientes->isEmpty() || $empresas->isEmpty()) {
            return;
        }
        foreach ($clientes as $cliente) {
            // Crea entre 1 y 3 facturas por cliente
            for ($i = 0; $i < rand(1, 3); $i++) {
                Factura::create([
                    'cliente_id' => $cliente->id,
                    'empresa_id' => $empresas->random()->id,
                    'fecha_factura' => Carbon::now()->subDays(rand(1, 365)),
                ]);
            }
        }
    }
}
