<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// El nombre de la clase es "detalle_factura" (minúsculas y guion bajo)
class detalle_factura extends Model
{
    use HasFactory, SoftDeletes;

    // Le decimos a Laravel que el nombre de la tabla es este.
    protected $table = 'detalle_factura';

    protected $fillable = [
        'factura_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'sub_total',
    ];

    // Relación con la factura principal
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    // Relación con el producto del inventario
    public function producto()
    {
        return $this->belongsTo(InventarioProductos::class, 'producto_id');
    }
}
