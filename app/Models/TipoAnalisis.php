<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoAnalisis extends Model
{
    /** @use HasFactory<\Database\Factories\TipoAnalisisFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_analisis';

    protected $fillable = [
        'nombre_analisis',
        'descripcion',
        'unidad_id',
        'created_by',
        'updated_by',
    ];

    public function unidad()
    {
        return $this->belongsTo(UnidadDeMedidas::class, 'unidad_id');
    }

    public function analisis()
    {
        return $this->hasMany(AnalisisCalidad::class, 'tipo_analisis_id');
    }
}