<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mantenimiento_equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_tarea_id')->constrained('tipo_tarea_limpieza_mantenimientos')->onDelete('cascade');
            $table->foreignId('productos_id')->nullable()->constrained('productos')->onDelete('set null');
            $table->text('descripcion_tarea');
            $table->dateTime('fecha_hora_programada');
            $table->dateTime('fecha_hora_realizada')->nullable();
            $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'REALIZADO', 'NO_REALIZADO']);
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_equipos');
    }
};
