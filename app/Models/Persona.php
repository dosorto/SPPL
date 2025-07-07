<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends Model
{
    /** @use HasFactory<\Database\Factories\PersonaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'personas';

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'dni',
        'direccion',
        'departamento_id',
        'municipio_id',
        'telefono',
        'sexo',
        'fecha_nacimiento',
        'pais_id',
        'fotografia', // Campo para la ruta de la fotografía
        'empresa_id', // Nueva relación
        'created_by',
        'updated_by',
    ];

    /**
     * Define la relación: una persona pertenece a un municipio.
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function departamento()
    {
    return $this->belongsTo(Departamento::class);
    }



    /**
     * Define la relación: una persona pertenece a un país.
     */
    public function pais()
    {
        return $this->belongsTo(Paises::class);
    }

    /**
     * Una persona puede ser un cliente (relación uno a uno).
     */
    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    /**
     * Una persona puede ser un empleado (relación uno a uno).
     */
    public function empleado()
    {
        return $this->hasOne(Empleado::class);
    }

    /**
     * Una persona pertenece a una empresa (relación 1:1).
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}

