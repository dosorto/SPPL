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
        Schema::create('detalle_nominas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomina_id')->constrained('nominas');
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->decimal('sueldo_bruto', 10, 2);
            $table->decimal('deducciones', 10, 2)->default(0);
            $table->text('deducciones_detalle')->nullable();
            $table->decimal('percepciones', 10, 2)->default(0);
            $table->text('percepciones_detalle')->nullable();
            $table->decimal('sueldo_neto', 10, 2);
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
        Schema::dropIfExists('detalle_nominas');
    }
};
