<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaUnidad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias_unidades';

    protected $fillable = [
        'nombre',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function unidadesMedidas()
    {
        return $this->hasMany(UnidadMedida::class, 'categoria_id');
    }
}
