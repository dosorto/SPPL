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
        Schema::create('categorias_clientes_productos_especificos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoria_cliente_id');
            $table->unsignedBigInteger('productos_id');
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Claves foráneas con nombres cortos
            $table->foreign('categoria_cliente_id', 'fk_ccpe_categoria_cliente')
                ->references('id')->on('categorias_clientes')->onDelete('cascade');
            $table->foreign('productos_id', 'fk_ccpe_producto')
                ->references('id')->on('productos')->onDelete('cascade');

            // Índices y restricciones
            $table->unique(['categoria_cliente_id', 'productos_id'], 'unique_cat_prod');
            $table->index(['categoria_cliente_id', 'activo'], 'idx_cat_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias_clientes_productos_especificos');
    }
};
