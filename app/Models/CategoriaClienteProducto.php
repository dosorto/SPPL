<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaClienteProducto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias_clientes_productos';

    protected $fillable = [
        'categoria_cliente_id',
        'categoria_producto_id',
        'descuento_porcentaje',
        'activo',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Relación con CategoriaCliente
     */
    public function categoriaCliente()
    {
        return $this->belongsTo(CategoriaCliente::class);
    }

    /**
     * Relación con CategoriaProducto
     */
    public function categoriaProducto()
    {
        return $this->belongsTo(CategoriaProducto::class);
    }
}
