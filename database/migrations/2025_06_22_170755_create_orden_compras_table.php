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
        Schema::create('orden_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_orden_compra_id')->constrained('tipo_orden_compras');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->date('fecha_realizada');
            $table->string('estado')->default('Pendiente');
            $table->text('descripcion')->nullable();
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
        Schema::dropIfExists('orden_compras');
    }
};