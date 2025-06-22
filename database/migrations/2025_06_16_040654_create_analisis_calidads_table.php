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
           Schema::create('analisis_calidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestra_id')->constrained('muestras')->onDelete('restrict');
            $table->foreignId('tipo_analisis_id')->constrained('tipo_analisis')->onDelete('restrict');
            $table->decimal('valor', 8, 2)->comment('Resultado del análisis');
            $table->string('observaciones')->nullable();
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
        Schema::dropIfExists('analisis_calidads');
    }
};
