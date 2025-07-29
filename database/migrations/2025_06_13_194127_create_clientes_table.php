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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_cliente', 255)->unique(); // Número de cliente
            $table->string('rtn', 20)->nullable(); // RTN del cliente

            // Relación uno a uno con personas
            $table->foreignId('persona_id')->constrained('personas'); 
            // Relación opcional con empresas
            $table->foreignId('empresa_id')
                  ->nullable() // ¡Este campo es opcional!
                  ->constrained('empresas')
                  ->onDelete('set null'); // Si se elimina una empresa, se establece a NULL en los clientes
            
            // Relación con categorías de clientes
            $table->foreignId('categoria_cliente_id')->nullable()->constrained('categorias_clientes');

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
        Schema::dropIfExists('clientes');
    }
};
