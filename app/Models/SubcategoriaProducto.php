<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcategoriaProducto extends Model
{
    use HasFactory, TenantScoped;

    protected $table = 'subcategorias_productos';

    protected $fillable = [
        'nombre',
        'categoria_id',
        'empresa_id',
        'created_by',
        'updated_by',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function productos()
    {
        return $this->hasMany(Productos::class, 'subcategoria_id');
    }
}