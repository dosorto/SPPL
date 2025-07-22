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
        Schema::create('empleado_deducciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empleado_id')->constrained('empleados');
            $table->foreignId('deduccion_id')->constrained('deducciones');
            $table->date('fecha_aplicacion')->default(DB::raw('CURRENT_DATE'));

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
        Schema::dropIfExists('empleado_deducciones');
    }
};
