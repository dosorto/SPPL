<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('producto_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->string('url'); // Aquí se guarda la ruta a la imagen
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_fotos');
    }
};
