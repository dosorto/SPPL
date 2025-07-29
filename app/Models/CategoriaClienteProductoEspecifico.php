<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaClienteProductoEspecifico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias_clientes_productos_especificos';

    protected $fillable = [
        'categoria_cliente_id',
        'productos_id',
        'descuento_porcentaje',
        'activo',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'descuento_porcentaje' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Relación con CategoriaCliente
     */
    public function categoriaCliente()
    {
        return $this->belongsTo(CategoriaCliente::class);
    }

    /**
     * Relación con Producto
     */
    public function producto()
    {
        return $this->belongsTo(Productos::class, 'productos_id');
    }
}
