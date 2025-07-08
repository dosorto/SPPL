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
        Schema::create('departamento_empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_departamento_empleado', 100); 
            $table->text('descripcion', 200)->nullable(); 

            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->unique(['nombre_departamento_empleado'], 'departamento_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamento_empleados');
    }
};
