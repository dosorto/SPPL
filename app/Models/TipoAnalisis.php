<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoAnalisis extends Model
{
    /** @use HasFactory<\Database\Factories\TipoAnalisisFactory> */
    use HasFactory;
     protected $table = 'tipos_analisis'; 
}
