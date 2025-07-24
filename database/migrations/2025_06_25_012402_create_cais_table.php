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
        Schema::create('cais', function (Blueprint $table) {
            $table->id();

            // --- Datos del CAI ---
            $table->string('cai')->unique()->comment('El código de autorización de impresión.');
            $table->foreignId('empresa_id')->constrained('empresas')->comment('Empresa a la que pertenece el CAI.');
            
            // --- Rango de Facturación ---
            $table->unsignedBigInteger('rango_inicial');
            $table->unsignedBigInteger('rango_final');
            $table->unsignedBigInteger('numero_actual')->comment('El último número de factura utilizado de este rango.');

            // --- Validez ---
            $table->date('fecha_limite_emision');
            $table->boolean('activo')->default(true)->comment('Indica si este CAI es el que se debe usar para facturar.');

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
        Schema::dropIfExists('cais');
    }
};
