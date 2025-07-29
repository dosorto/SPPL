<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Factura;
use Carbon\Carbon;

class CreateSampleFacturas extends Command
{
    protected $signature = 'create:sample-facturas';
    protected $description = 'Crear facturas de ejemplo';

    public function handle()
    {
        $this->info('Creando facturas de ejemplo...');
        
        // Obtener los primeros 10 clientes
        $clientes = Cliente::take(10)->get();
        
        foreach ($clientes as $cliente) {
            // Crear 2-3 facturas por cliente
            for ($i = 0; $i < rand(2, 4); $i++) {
                $factura = Factura::create([
                    'cliente_id' => $cliente->id,
                    'empresa_id' => 1, // Usar empresa ID 1 como fallback
                    'empleado_id' => 1, // Usar empleado ID 1 como fallback
                    'fecha_factura' => Carbon::now()->subDays(rand(1, 90)),
                    'estado' => ['Pendiente', 'Pagada', 'Vencida'][rand(0, 2)], // Usar valores del ENUM
                    'subtotal' => $subtotal = rand(500, 5000),
                    'impuestos' => $impuestos = $subtotal * 0.15,
                    'total' => $subtotal + $impuestos,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                
                $this->info("Factura #{$factura->id} creada para cliente {$cliente->numero_cliente}");
            }
        }
        
        $this->info('✅ Facturas de ejemplo creadas exitosamente!');
    }
}
