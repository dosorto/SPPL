<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoFoto extends Model
{
    use HasFactory;

    protected $fillable = ['producto_id', 'url'];

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }
}
