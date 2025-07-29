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
        Schema::create('detalle_factura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas');
            $table->foreignId('producto_id')->constrained('inventario_productos');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento_aplicado', 5, 2)->nullable();
            $table->decimal('sub_total', 10, 2);
            $table->decimal('isv_aplicado', 5, 2)->default(0)->comment('El % de ISV aplicado en el momento de la venta');
            $table->decimal('costo_unitario', 10, 2)->comment('Congela el costo del producto al momento de la venta.');
            $table->decimal('utilidad_unitaria', 10, 2)->comment('Congela la ganancia (precio - costo) al momento de la venta.');
            // Campos de logs
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at
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
        Schema::dropIfExists('detalle_factura');
    }
};
