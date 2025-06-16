<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaUnidad extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriaUnidadFactory> */
    use HasFactory;
    protected $table = 'categorias_unidades'; 
}
