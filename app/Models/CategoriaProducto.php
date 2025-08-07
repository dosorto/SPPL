<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaProducto extends Model
{
    use HasFactory;

    protected $table = 'categorias_productos';

    protected $fillable = [
        'nombre',
        'created_by',
        'updated_by',
    ];

    public function subcategorias()
    {
        return $this->hasMany(SubcategoriaProducto::class, 'categoria_id');
    }

    public function productos()
    {
        return $this->hasMany(Productos::class, 'categoria_id');
    }

    /**
     * Una categoría de producto tiene muchas relaciones con categorías de clientes.
     */
    public function categoriasClientes()
    {
        return $this->belongsToMany(CategoriaCliente::class, 'categorias_clientes_productos', 'categoria_producto_id', 'categoria_cliente_id')
            ->withPivot('descuento_porcentaje', 'activo')
            ->withTimestamps();
    }
}