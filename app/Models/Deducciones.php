<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class Deducciones extends Model
{
    /** @use HasFactory<\Database\Factories\DeduccionesFactory> */
        use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'deducciones';

    protected $fillable = [
        'deduccion',
        'valor',
        'tipo_valor',
        'empresa_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    public function deduccionesAplicadas()
    {
        return $this->hasMany(EmpleadoDeducciones::class, 'empleado_id')->with('deduccion');
    }

        public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
