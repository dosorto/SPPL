<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
   public function up(): void
{
    Schema::create('mantenimiento_equipos', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('tipo_tarea_id');
        $table->unsignedBigInteger('productos_id')->nullable();
        $table->text('descripcion_tarea');
        $table->dateTime('fecha_hora_programada');
        $table->dateTime('fecha_hora_realizada');
        $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'REALIZADO', 'NO_REALIZADO']);
        $table->unsignedBigInteger('empleado_id');
        $table->text('observaciones')->nullable();

        // Auditoría
        $table->timestamps();
        $table->softDeletes();
        $table->integer('created_by')->nullable();
        $table->integer('updated_by')->nullable();
        $table->integer('deleted_by')->nullable();

        // Llaves foráneas
        $table->foreign('tipo_tarea_id')->references('id')->on('tipo_tarea_limpieza_mantenimiento');
        $table->foreign('productos_id')->references('id')->on('productos');
        $table->foreign('empleado_id')->references('id')->on('empleados');
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
