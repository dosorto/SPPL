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
        Schema::create('categorias_clientes_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_cliente_id')->constrained('categorias_clientes')->onDelete('cascade');
            $table->foreignId('categoria_producto_id')->constrained('categorias_productos')->onDelete('cascade');
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            // Unique constraint to prevent duplicate relationships
            $table->unique(['categoria_cliente_id', 'categoria_producto_id'], 'cat_cliente_producto_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias_clientes_productos');
    }
};
