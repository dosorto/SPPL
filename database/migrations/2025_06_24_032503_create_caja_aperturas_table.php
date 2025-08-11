<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_aperturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('empresa_id')->constrained();
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('monto_final_calculado', 10, 2)->nullable();
            $table->json('conteo_usuario')->nullable();

            $table->json('diferencias_cierre')->nullable();

            // Campo de texto para las notas u observaciones del cierre.
            $table->text('notas_cierre')->nullable();
            $table->timestamp('fecha_apertura');
            $table->timestamp('fecha_cierre')->nullable();
            $table->string('estado', 20)->default('ABIERTA');
            
            
            $table->timestamps(); 
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_aperturas');
    }
};
