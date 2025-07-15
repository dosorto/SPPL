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
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');
            $table->string('descripcion')->nullable();

            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');

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
