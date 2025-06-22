<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadDeMedidas extends Model
{
    /** @use HasFactory<\Database\Factories\UnidadDeMedidasFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'unidad_de_medidas';

    protected $fillable = [
        'nombre',
        'abreviacion',
        'categoria_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaUnidades::class, 'categoria_id');
    }

    public function muestras()
    {
        return $this->hasMany(Muestra::class, 'unidades_id');
    }

    public function tipoAnalisis()
    {
        return $this->hasMany(TipoAnalisis::class, 'unidad_id');
    }
}
