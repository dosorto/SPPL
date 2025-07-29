<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_aperturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('caja_id')->constrained('cajas'); // Asegúrate de tener tabla cajas
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('user_id')->constrained('users'); // Usuario que abre la caja

            $table->decimal('monto_inicial', 10, 2);
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');

            $table->timestamp('fecha_apertura')->default(now());
            $table->timestamp('fecha_cierre')->nullable();

            $table->timestamps();       // created_at, updated_at
            $table->softDeletes();      // deleted_at
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_aperturas');
    }
};
