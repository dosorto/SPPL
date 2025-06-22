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
       Schema::create('tipos_analisis', function (Blueprint $table) {
    $table->id();
    $table->string('nombre_analisis')->comment('Nombre del tipo de análisis, como "Grasa" o "pH"');
    $table->text('descripcion')->nullable()->comment('Descripción del análisis, por ejemplo "Mide el porcentaje de grasa"');
    $table->foreignId('unidad_id')->constrained('unidades_medidas')->onDelete('restrict');
    
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
        Schema::dropIfExists('tipo_analises');
    }
};
