<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubcategoriaProducto extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'subcategorias_productos';

    protected $fillable = [
        'nombre',
        'categoria_id',
        'empresa_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }

    public function productos()
    {
        return $this->hasMany(Productos::class, 'subcategoria_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->empresa_id && auth()->check()) {
                $model->empresa_id = auth()->user()->empresa_id;
            }
        });
    }
}