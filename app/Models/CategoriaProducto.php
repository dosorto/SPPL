<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaProducto extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'categorias_productos';

    protected $fillable = [
        'nombre',
        'empresa_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function subcategorias()
    {
        return $this->hasMany(SubcategoriaProducto::class, 'categoria_id');
    }

    public function productos()
    {
        return $this->hasMany(Productos::class, 'categoria_id');
    }

    public function categoriasClientes()
    {
        return $this->belongsToMany(CategoriaCliente::class, 'categorias_clientes_productos', 'categoria_producto_id', 'categoria_cliente_id')
            ->withPivot('descuento_porcentaje', 'activo')
            ->withTimestamps();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->empresa_id && auth()->check()) {
                $model->empresa_id = auth()->user()->empresa_id;
            }
        });
    }
}