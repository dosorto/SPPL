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
            $table->string('nombre_analisis', 255);

            // Clave foránea
            $table->foreignId('unidad_id')->constrained('unidades_medidas');

            $table->decimal('rango_min', 8, 2);
            $table->decimal('rango_max', 8, 2);

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
