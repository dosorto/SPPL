<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadMedida extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unidades_medidas';

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
        return $this->belongsTo(CategoriaUnidad::class, 'categoria_id');
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

