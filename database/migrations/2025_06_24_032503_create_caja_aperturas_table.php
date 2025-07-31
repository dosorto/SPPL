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
            
            // Relaciona con el usuario que abre la caja.
            $table->foreignId('user_id')->constrained();

            // Monto inicial (L. 2000.00 por defecto).
            $table->decimal('monto_inicial', 10, 2);

            // Se llenará al momento de cerrar la caja. Nulable mientras esté abierta.
            $table->decimal('monto_final_calculado', 10, 2)->nullable();
            
            // Timestamp de apertura. Se establece al crear el registro.
            $table->timestamp('fecha_apertura');

            // Timestamp de cierre. Nulable mientras la caja esté abierta.
            $table->timestamp('fecha_cierre')->nullable();

            // Estado para saber si la caja está 'ABIERTA' o 'CERRADA'.
            $table->string('estado', 20)->default('ABIERTA');
            
            $table->timestamps(); // Laravel gestiona created_at y updated_at
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_aperturas');
    }
};
