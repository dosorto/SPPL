<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendimiento_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rendimiento_id')->constrained('rendimientos');
            $table->foreignId('producto_id')->constrained('productos');
            $table->float('cantidad');
            $table->foreignId('unidad_de_medida_id')->constrained('unidad_de_medidas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendimiento_productos');
    }
};
