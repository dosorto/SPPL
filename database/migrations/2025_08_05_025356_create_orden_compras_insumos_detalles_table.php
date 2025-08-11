<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_compras_insumos_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_insumo_id')->constrained('orden_compras_insumos')->onDelete('cascade');
            $table->foreignId('tipo_orden_compra_id')->nullable()->constrained('tipo_orden_compras')->onDelete('restrict');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('porcentaje_grasa', 5, 2)->nullable();
            $table->decimal('porcentaje_proteina', 5, 2)->nullable();
            $table->decimal('porcentaje_humedad', 5, 2)->nullable();
            $table->boolean('anomalias')->default(false);
            $table->text('detalles_anomalias')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_compras_insumos_detalles');
    }
};