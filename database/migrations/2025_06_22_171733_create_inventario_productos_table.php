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
        Schema::create('inventario_productos', function (Blueprint $table) {
        $table->id();

        $table->foreignId('producto_id')->constrained('productos');
        $table->float('cantidad')->nullable();
        $table->float('precio_costo');
        $table->float('precio_detalle');
        $table->float('precio_promocion');
        $table->float('precio_mayorista');
        $table->foreignId('empresa_id')->constrained('empresas');
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
        Schema::dropIfExists('inventario_productos');
    }
};
