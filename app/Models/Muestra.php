<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Muestra extends Model
{
    protected $table = 'muestras';

    public function unidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidades_id');
    }

    public function analisis()
    {
        return $this->hasMany(AnalisisCalidad::class, 'muestra_id');
    }
}

