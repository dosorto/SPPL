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
            $table->foreignId('producto_id')->constrained('productos');
            $table->text('descripcion')->nullable();
            $table->date('fecha_programada');
            $table->date('fecha_realizada');
            $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'REALIZADO', 'NO_REALIZADO']);
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->text('observaciones');
            // Auditoría
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
