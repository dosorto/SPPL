<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class DetalleNominas extends Model
{

        use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'detalle_nominas';

    protected $fillable = [
        'nomina_id',
        'empleado_id',
        'sueldo_bruto',
        'empresa_id',
        'deducciones',
        'deducciones_excluidas', // Usaremos un campo existente o ignorar este atributo
        'percepciones',
        'sueldo_neto',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Variable para almacenar deducciones excluidas en la sesión
    protected $deduccionesExcluidas = [];

    public function nomina()
    {
        return $this->belongsTo(Nominas::class, 'nomina_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
