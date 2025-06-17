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
        Schema::create('tipo_orden_compras', function (Blueprint $table) {
        $table->id();
        $table->string('nombre', 100);

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
        Schema::dropIfExists('tipo_orden_compras');
    }
};
