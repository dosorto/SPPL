<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventarioProductos extends Model
{
    /** @use HasFactory<\Database\Factories\InventarioProductosFactory> */
    use HasFactory;

    protected $table = 'inventario_productos';

    protected $fillable = [
    'producto_id',           // FK a productos
    'cantidad',
    'precio_costo',
    'precio_detalle',
    'precio_promocion',
    'created_by',
    'updated_by',
    'deleted_by',
];


    public function producto()
    {
        return $this->belongsTo(Productos::class);
    }
}
