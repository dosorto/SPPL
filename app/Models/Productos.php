<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Productos extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'unidad_de_medida_id',
        'nombre',
        'descripcion',
        'descripcion_corta',
        'sku',
        'codigo',
        'color',
        'isv',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function unidadDeMedida()
    {
        return $this->belongsTo(UnidadDeMedidas::class, 'unidad_de_medida_id');
    }

    public function fotosRelacion()
    {
        return $this->hasMany(ProductoFoto::class, 'producto_id');
    }
}
