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
        Schema::create('ordenes_produccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->integer('cantidad');
            $table->date('fecha_solicitud');
            $table->date('fecha_entrega')->nullable();
            $table->enum('estado', ['Pendiente', 'En Proceso', 'Finalizada', 'Cancelada'])->default('Pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
        });

        Schema::create('orden_produccion_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_produccion_id')->constrained('ordenes_produccion')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('productos');
            $table->integer('cantidad_utilizada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_produccion_insumos');
        Schema::dropIfExists('ordenes_produccion');
    }
};
