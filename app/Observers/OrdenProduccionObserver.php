<?php

namespace App\Observers;

use App\Models\OrdenProduccion;
use App\Models\Rendimiento;
use App\Models\InventarioProductos;
use App\Models\InventarioInsumos;
use App\Models\MovimientoInventario;

class OrdenProduccionObserver
{
    public function updated(OrdenProduccion $orden)
    {
        // Si la orden pasa a estado Finalizada y no tiene rendimiento, lo crea
        if ($orden->estado === 'Finalizada' && !$orden->rendimiento) {
            Rendimiento::create([
                'orden_produccion_id' => $orden->id,
                'cantidad_mp' => 0, // Ajustar según lógica de negocio
                'precio_mp' => 0,
                'precio_otros_mp' => 0,
                'margen_ganancia' => 0,
                'created_by' => auth()->id() ?? null,
            ]);
        }
    }
}
