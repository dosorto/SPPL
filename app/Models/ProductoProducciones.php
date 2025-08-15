<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class ProductoProducciones extends Model
{
    use HasFactory, TenantScoped, SoftDeletes;

    // La migración creó la tabla `producto_producciones`
    protected $table = 'producto_producciones';

    protected $fillable = [
        'rendimientos_id',
        'producto_id',
        'cantidad',
        'unidades_id',
        'estado',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function producto()
    {
        return $this->belongsTo(\App\Models\Productos::class, 'producto_id');
    }

    // La migración usa rendimientos_id (plural) por eso la especifcamos:
    public function rendimiento()
    {
        return $this->belongsTo(\App\Models\Rendimiento::class, 'rendimientos_id');
    }

    // La migración usa unidades_id que referencia unidad_de_medidas
    public function unidadMedida()
    {
        return $this->belongsTo(\App\Models\UnidadDeMedidas::class, 'unidades_id');
    }
}