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
         Schema::create('orden_produccion_insumos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('orden_produccion_id')
                ->constrained('ordenes_produccion');

            $table->foreignId('insumo_id')->constrained('productos');
            $table->integer('cantidad_utilizada');

            // 👇 si también quieres guardar UDM del insumo
            $table->foreignId('unidad_de_medida_id')
                ->constrained('unidad_de_medidas');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_produccion_insumos');
    }
};
