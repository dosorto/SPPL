<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;
class Percepciones extends Model
{
        use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'percepciones';

        protected $fillable = [
        'percepcion',
        'empresa_id',
        'valor',
        'created_by',
        'updated_by',
        'deleted_by',

    ];


    public function percepcionesAplicadas()
    {
        return $this->hasMany(EmpleadoPercepciones::class, 'empleado_id')->with('deduccion');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
