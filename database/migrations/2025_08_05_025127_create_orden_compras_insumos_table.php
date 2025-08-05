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
            $table->foreignId('tipo_orden_compra_id')->constrained('tipo_orden_compras');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->date('fecha_realizada');
            $table->string('estado')->default('Pendiente');
            $table->text('descripcion')->nullable();
            // Quality Analysis Fields
            $table->decimal('porcentaje_grasa', 5, 2)->nullable(); // e.g., 3.50%
            $table->decimal('porcentaje_proteina', 5, 2)->nullable(); // e.g., 2.80%
            $table->decimal('porcentaje_humedad', 5, 2)->nullable(); // e.g., 87.00%
            $table->boolean('anomalias')->default(false); // Checklist: true (with anomalies), false (no anomalies)
            $table->text('detalles_anomalias')->nullable(); // Description of anomalies, if any
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