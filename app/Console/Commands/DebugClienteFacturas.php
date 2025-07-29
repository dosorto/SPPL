<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Factura;

class DebugClienteFacturas extends Command
{
    protected $signature = 'debug:cliente-facturas {cliente_id?}';
    protected $description = 'Debug cliente y sus facturas';

    public function handle()
    {
        $clienteId = $this->argument('cliente_id');
        
        $this->info('=== DEBUG CLIENTE FACTURAS ===');
        
        // Verificar si hay clientes
        $totalClientes = Cliente::count();
        $this->info("Total de clientes: {$totalClientes}");
        
        // Verificar si hay facturas
        $totalFacturas = Factura::count();
        $this->info("Total de facturas: {$totalFacturas}");
        
        if ($clienteId) {
            $cliente = Cliente::with('facturas')->find($clienteId);
            if ($cliente) {
                $this->info("Cliente encontrado: {$cliente->numero_cliente}");
                $this->info("Facturas del cliente: " . $cliente->facturas->count());
                
                foreach ($cliente->facturas as $factura) {
                    $this->info("- Factura ID: {$factura->id}, Total: {$factura->total}, Fecha: {$factura->fecha_factura}");
                }
            } else {
                $this->error("Cliente con ID {$clienteId} no encontrado");
            }
        } else {
            // Mostrar algunos clientes con sus facturas
            $clientes = Cliente::with('facturas')->take(5)->get();
            foreach ($clientes as $cliente) {
                $this->info("Cliente: {$cliente->numero_cliente} - Facturas: " . $cliente->facturas->count());
            }
        }
        
        // Verificar estructura de tabla facturas
        $facturasSample = Factura::take(3)->get();
        $this->info("\n=== SAMPLE FACTURAS ===");
        foreach ($facturasSample as $factura) {
            $this->info("ID: {$factura->id}, Cliente ID: {$factura->cliente_id}, Total: {$factura->total}");
        }
    }
}
