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

            // Claves foráneas
            $table->foreignId('muestra_id')->constrained('muestras');
            $table->foreignId('tipo_analisis_id')->constrained('tipos_analisis');

            $table->decimal('valor', 8, 2);
            $table->text('observaciones', 255)->nullable();

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
        Schema::dropIfExists('analisis_calidads');
    }
};
