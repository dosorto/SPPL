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

        $table->foreignId('nomina_id')->constrained('nominas')->onDelete('cascade');
        $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');

        $table->decimal('sueldo_bruto', 10, 2);
        $table->decimal('deducciones', 10, 2)->default(0);
        $table->unsignedSmallInteger('total_horas_extra')->default(0);
        $table->decimal('horas_extra_monto', 10, 2)->default(0);
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
