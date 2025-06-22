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
        Schema::create('orden_producciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis_calidads');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('cantidad_solicitada', 10, 2);
            $table->foreignId('unidades_id')->constrained('unidades_medidas');
            $table->string('estado', 20);
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_total', 10, 2);
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
        Schema::dropIfExists('orden_producciones');
    }
};
