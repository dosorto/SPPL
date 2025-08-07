<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcategoriaProducto extends Model
{
    use HasFactory;

    protected $table = 'subcategorias_productos';

    protected $fillable = [
        'nombre',
        'categoria_id',
        'created_by',
        'updated_by',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }

    public function productos()
    {
        return $this->hasMany(Productos::class, 'subcategoria_id');
    }
}