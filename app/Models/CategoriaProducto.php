<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaProducto extends Model
{
    use HasFactory, TenantScoped;

    protected $table = 'categorias_productos';

    protected $fillable = [
        'nombre',
        'empresa_id',
        'created_by',
        'updated_by',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function subcategorias()
    {
        return $this->hasMany(SubcategoriaProducto::class, 'categoria_id');
    }

    public function productos()
    {
        return $this->hasMany(Productos::class, 'categoria_id');
    }
}