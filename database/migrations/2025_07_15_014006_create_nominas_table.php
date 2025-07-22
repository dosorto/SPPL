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
        Schema::create('nominas', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('mes'); 
            $table->year('año'); 
            $table->string('descripcion')->nullable();
            $table->boolean('cerrada')->default(false);

            $table->foreignId('empresa_id')->constrained('empresas');

            // Nuevos campos de detalle_nominas
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->onDelete('cascade');
            $table->decimal('sueldo_bruto', 12, 2)->nullable();
            $table->decimal('deducciones', 12, 2)->nullable();
            $table->decimal('percepciones', 12, 2)->nullable();
            $table->decimal('sueldo_neto', 12, 2)->nullable();

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
        Schema::dropIfExists('nominas');
    }
};
