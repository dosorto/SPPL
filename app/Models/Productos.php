<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Productos extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'productos';

    protected $fillable = [
        'unidad_de_medida_id',
        'categoria_id',
        'subcategoria_id',
        'nombre',
        'descripcion',
        'descripcion_corta',
        'sku',
        'codigo',
        'color',
        'isv',
        'empresa_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function unidadDeMedida()
    {
        return $this->belongsTo(UnidadDeMedidas::class, 'unidad_de_medida_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }

    public function subcategoria()
    {
        return $this->belongsTo(SubcategoriaProducto::class, 'subcategoria_id');
    }

    public function fotosRelacion()
    {
        return $this->hasMany(ProductoFoto::class, 'producto_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}