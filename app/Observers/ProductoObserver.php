<?php

namespace App\Observers;

use App\Models\Productos;
use App\Models\InventarioProductos;

class ProductoObserver
{
    /**
     * Handle the Productos "created" event.
     */
    public function created(Productos $producto): void
    {
        InventarioProductos::create([
            'producto_id'      => $producto->id,
            'cantidad'         => 0,
            'precio_costo'     => 0,
            'precio_detalle'   => 0,
            'precio_promocion' => 0,
            'precio_mayorista' => 0,
        ]);
    }

    /**
     * Handle the Productos "updated" event.
     */
    public function updated(Productos $productos): void
    {
        //
    }

    /**
     * Handle the Productos "deleted" event.
     */
    public function deleted(Productos $productos): void
    {
        //
    }

    /**
     * Handle the Productos "restored" event.
     */
    public function restored(Productos $productos): void
    {
        //
    }

    /**
     * Handle the Productos "force deleted" event.
     */
    public function forceDeleted(Productos $productos): void
    {
        //
    }
}
