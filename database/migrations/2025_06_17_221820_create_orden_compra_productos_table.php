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
        Schema::create('orden_compra_productos', function (Blueprint $table) {
        $table->id();

        $table->foreignId('producto_id')->constrained('productos');
        $table->foreignId('orden_compra_id')->constrained('orden_compras');
        $table->float('cantidad')->nullable();
        $table->float('precio')->nullable();
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
        Schema::dropIfExists('orden_compra_productos');
    }
};
