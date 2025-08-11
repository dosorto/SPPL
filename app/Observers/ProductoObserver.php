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
            'empresa_id'       => $producto->empresa_id,
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
    public function deleted(Productos $producto): void
    {
        // Eliminar el registro de inventario asociado
        InventarioProductos::where('producto_id', $producto->id)->delete();
    }

    /**
     * Handle the Productos "restored" event.
     */
    public function restored(Productos $producto): void
    {
        // Si el producto se restaura, también restaurar/crear el inventario
        InventarioProductos::firstOrCreate(
            ['producto_id' => $producto->id],
            [
                'cantidad'         => 0,
                'empresa_id'       => $producto->empresa_id,
                'precio_costo'     => 0,
                'precio_detalle'   => 0,
                'precio_promocion' => 0,
                'precio_mayorista' => 0,
            ]
        );
    }

    /**
     * Handle the Productos "force deleted" event.
     */
    public function forceDeleted(Productos $producto): void
    {
        // Cuando se elimina permanentemente un producto, también eliminar su inventario
        InventarioProductos::where('producto_id', $producto->id)->forceDelete();
    }
}