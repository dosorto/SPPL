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
            $table->string('numero_empleado', 255);
            $table->date('fecha_ingreso');
            $table->decimal('salario', 10, 2);
            $table->json('deducciones_aplicables')->nullable();

            $table->foreignId('persona_id')->constrained('personas');
            $table->foreignId('departamento_empleado_id')->constrained('departamento_empleados');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('tipo_empleado_id')->constrained('tipo_empleados');

            $table->timestamps();
            $table->softDeletes();
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
