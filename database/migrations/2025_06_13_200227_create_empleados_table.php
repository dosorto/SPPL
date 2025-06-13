<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('numero_empleado', 255)->unique(); // Número de empleado
            $table->date('fecha_ingreso');
            $table->decimal('salario', 10, 2); // DECIMAL para precisión en moneda

            // Relación uno a uno con personas
            $table->foreignId('persona_id')
                  ->unique() // Una persona solo puede ser un empleado
                  ->constrained('personas')
                  ->onDelete('cascade'); // Si se elimina la persona, se elimina el empleado

            // Relaciones con otras tablas
            $table->foreignId('departamento_empleado_id') // Es el departamento interno de la empresa
                  ->constrained('departamento_empleados')
                  ->onDelete('cascade'); // Si se elimina el departamento, se eliminan los empleados

            $table->foreignId('empresa_id')
                  ->constrained('empresas')
                  ->onDelete('cascade'); // Si se elimina la empresa, se eliminan los empleados

            $table->foreignId('tipo_empleado_id')
                  ->constrained('tipo_empleados')
                  ->onDelete('cascade'); // Si se elimina el tipo de empleado, se eliminan los empleados

            // Campos de logs
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
