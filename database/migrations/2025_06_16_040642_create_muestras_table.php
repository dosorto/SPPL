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
        Schema::create('muestras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_productos_id')->constrained('inventario_productos');
            $table->string('nombre_muestra', 255);
            $table->decimal('cantidad', 8, 2);

            // Clave foránea
            $table->foreignId('unidades_id')->constrained('unidades_medidas');

            $table->decimal('temperatura', 5, 2);
            $table->date('fecha_muestra');

            // Auditoría
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
        Schema::dropIfExists('muestras');
    }
};
