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
        Schema::create('proveedores', function (Blueprint $table) {
        $table->id();
        $table->string('nombre_proveedor', 255);
        $table->string('telefono', 20)->nullable();
        $table->string('rtn', 20)->nullable();
        $table->text('direccion', 200)->nullable();
        $table->foreignId('municipio_id')->constrained('municipios');
        $table->string('persona_contacto', 255)->nullable();
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
        Schema::dropIfExists('proveedores');
    }
};
