<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnalisisCalidad extends Model
{
    /** @use HasFactory<\Database\Factories\AnalisisCalidadFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'analisis_calidad';

    protected $fillable = [
        'muestra_id',
        'tipo_analisis_id',
        'valor',
        'observaciones',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function muestra()
    {
        return $this->belongsTo(Muestras::class, 'muestra_id');
    }

    public function tipoAnalisis()
    {
        return $this->belongsTo(TiposAnalisis::class, 'tipo_analisis_id');
    }
}