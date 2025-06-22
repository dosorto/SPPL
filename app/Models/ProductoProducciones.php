<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoProducciones extends Model
{
    /** @use HasFactory<\Database\Factories\ProductoProduccionesFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'producto_produccion';

    protected $fillable = [
        'rendimientos_id',
        'unidades_id',
        'estado',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function rendimiento()
    {
        return $this->belongsTo(Rendimiento::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }
}
