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
        Schema::create('deducciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('empresa_id')->constrained('empresas');
        $table->string('deduccion');
        // Aquí el valor puede representar tanto un porcentaje como un monto
        $table->decimal('valor', 10, 2);

        // Nuevo campo para indicar si el valor es un porcentaje o un monto
        $table->enum('tipo_valor', ['porcentaje', 'monto'])->default('porcentaje');
        
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
        Schema::dropIfExists('deducciones');
    }
};
