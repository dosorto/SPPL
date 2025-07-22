<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_de_medida_id')->constrained('unidad_de_medidas')->onDelete('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_productos')->onDelete('set null');
            $table->foreignId('subcategoria_id')->nullable()->constrained('subcategorias_productos')->onDelete('set null');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->text('descripcion_corta')->nullable();
            $table->string('sku', 100)->nullable();
            $table->string('codigo', 100)->nullable();
            $table->string('color', 100)->nullable();
            $table->float('isv')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};