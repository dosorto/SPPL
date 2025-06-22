<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaUnidades extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriaUnidadesFactory> */
    use HasFactory;

    protected $table = 'categoria_unidades';

    protected $fillable = [
        'nombre',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
