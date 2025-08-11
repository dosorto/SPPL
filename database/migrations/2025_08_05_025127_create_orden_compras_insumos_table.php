<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_compras_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('restrict');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('restrict');
            $table->date('fecha_realizada');
            $table->string('estado')->default('Pendiente');
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_compras_insumos');
    }
};