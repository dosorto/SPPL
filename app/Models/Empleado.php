<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        'numero_empleado',
        'fecha_ingreso',
        'salario',
        'persona_id',
        'departamento_empleado_id', // FK a departamentos_empleados
        'empresa_id',
        'tipo_empleado_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'salario' => 'decimal:2',
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
        // 'deleted_at' => 'datetime',
    ];

    /**
     * Un empleado pertenece a una persona (relación uno a uno inversa).
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Un empleado pertenece a un departamento interno de una empresa.
     */
    public function departamento()
    {
        return $this->belongsTo(DepartamentoEmpleado::class, 'departamento_empleado_id'); // Asegura la FK
    }

    /**
     * Un empleado pertenece a una empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Un empleado tiene un tipo de empleado.
     */
    public function tipoEmpleado()
    {
        return $this->belongsTo(TipoEmpleado::class);
    }
}
