<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Empleado;
use App\Models\detalle_factura;
use App\Models\Productos;
use Carbon\Carbon;

class FacturaSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $empresas = Empresa::all();
        $productos = Productos::all();
        $empleados = Empleado::all();
        
        if ($clientes->isEmpty() || $empresas->isEmpty()) {
            return;
        }
        
        $estados = ['Pagada', 'Pendiente', 'Cancelada'];
        
        foreach ($clientes as $cliente) {
            // Crea entre 1 y 5 facturas por cliente
            for ($i = 0; $i < rand(1, 5); $i++) {
                $fecha = Carbon::now()->subDays(rand(1, 180));
                $empresa = $empresas->random();
                $empleado = $empleados->isEmpty() ? null : $empleados->random();
                
                $subtotal = 0;
                $impuestos = 0;
                
                // Crear factura
                $factura = Factura::create([
                    'cliente_id' => $cliente->id,
                    'empresa_id' => $empresa->id,
                    'empleado_id' => $empleado ? $empleado->id : 1, // Usar ID 1 como fallback
                    'fecha_factura' => $fecha,
                    'estado' => $estados[array_rand($estados)],
                    'subtotal' => 0, // Se actualizará después
                    'impuestos' => 0, // Se actualizará después
                    'total' => 0, // Se actualizará después
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                
                // Crear detalles de factura
                $numDetalles = rand(1, 5);
                for ($j = 0; $j < $numDetalles; $j++) {
                    if ($productos->isEmpty()) continue;
                    
                    $producto = $productos->random();
                    $cantidad = rand(1, 10);
                    $precio = $producto->precio_venta ?? rand(100, 500);
                    $importe = $cantidad * $precio;
                    
                    // Crear detalle
                    detalle_factura::create([
                        'factura_id' => $factura->id,
                        'producto_id' => $producto->id,
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'importe' => $importe,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]);
                    
                    $subtotal += $importe;
                }
                
                // Calcular impuestos (15%)
                $impuestos = $subtotal * 0.15;
                $total = $subtotal + $impuestos;
                
                // Actualizar factura con montos
                $factura->update([
                    'subtotal' => $subtotal,
                    'impuestos' => $impuestos,
                    'total' => $total
                ]);
            }
        }
    }
}
