<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Productos extends Model
{
    /** @use HasFactory<\Database\Factories\ProductosFactory> */
    use HasFactory,SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
    'unidad_de_medida_id',    // FK a unidad_de_medidas
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
    // En App\Models\Productos.php

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadDeMedidas::class, 'unidad_de_medida_id', 'id');
    }


}