<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Productos extends Model
{
    /** @use HasFactory<\Database\Factories\ProductosFactory> */
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
    'unidad_de_medida_id',    // FK a unidades_medidas
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

    public function unidadMedida()
    {
        return $this->hasMany(UnidadMedida::class, 'unidad_de_medida_id');
    }
}
