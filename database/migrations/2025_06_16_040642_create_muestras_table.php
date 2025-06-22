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
        Schema::create('muestras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_producto')->constrained('inventario_productos')->onDelete('restrict');
            $table->string('nombre_muestra')->comment('Nombre o identificación de la muestra');
            $table->decimal('cantidad', 8, 2);
            $table->foreignId('unidades_id')->constrained('unidades_medidas')->onDelete('restrict');
            $table->decimal('temperatura', 5, 2)->nullable()->comment('Temperatura de la muestra');
            $table->date('fecha_muestra')->comment('Fecha de toma de la muestra');
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
        Schema::dropIfExists('muestras');
    }
};
