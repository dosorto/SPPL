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
        Schema::create('rendimientos', function (Blueprint $table) {
            $table->id();
            // Clave foránea hacia orden_produccion
            $table->foreignId('orden_produccion_id')->constrained('orden_producciones');
            // Campos principales
            $table->decimal('cantidad_mp', 10, 2);
            $table->decimal('precio_mp', 10, 2);
            $table->decimal('precio_otros_mp', 10, 2);
            $table->float('margen_ganancia');

            // Campos de auditoría
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
        Schema::dropIfExists('rendimientos');
    }
};
