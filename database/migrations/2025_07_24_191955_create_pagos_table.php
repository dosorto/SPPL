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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            // --- Relaciones ---
            $table->foreignId('factura_id')->constrained('facturas');
            $table->foreignId('metodo_pago_id')->constrained('metodos_pagos');
            $table->foreignId('empresa_id')->constrained('empresas');

            // --- Datos del Pago ---
            $table->decimal('monto', 10, 2);
            $table->string('referencia')->nullable()->comment('Ej: # de transferencia, últimos 4 dígitos');
            $table->decimal('monto_recibido', 10, 2)->nullable();
            $table->decimal('cambio', 10, 2)->nullable();
            $table->timestamp('fecha_pago')->useCurrent(); // Usa la fecha y hora actual por defecto

            // --- Auditoría y Timestamps ---
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
