<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaCliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias_clientes';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
        'activo',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Una categoría de cliente tiene muchos clientes.
     */
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'categoria_cliente_id');
    }

    /**
     * Una categoría de cliente tiene muchas relaciones con categorías de productos.
     */
    public function categoriasProductos()
    {
        return $this->belongsToMany(CategoriaProducto::class, 'categorias_clientes_productos', 'categoria_cliente_id', 'categoria_producto_id')
            ->withPivot('descuento_porcentaje', 'activo')
            ->withTimestamps();
    }

    /**
     * Una categoría de cliente tiene muchos descuentos por productos específicos.
     */
    public function productosEspecificos()
    {
        return $this->hasMany(CategoriaClienteProductoEspecifico::class, 'categoria_cliente_id');
    }

    /**
     * Relación many-to-many con productos específicos a través de la tabla pivot.
     */
    public function productos()
    {
        return $this->belongsToMany(Productos::class, 'categorias_clientes_productos_especificos', 'categoria_cliente_id', 'productos_id')
            ->withPivot('descuento_porcentaje', 'activo')
            ->withTimestamps();
    }
}
