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
        Schema::create('producto_producciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rendimientos_id')->constrained('rendimientos');
            $table->foreignId('unidades_id')->constrained('unidad_de_medidas');

            $table->string('estado');

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
        Schema::dropIfExists('producto_producciones');
    }
};
