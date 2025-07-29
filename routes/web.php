<?php

use Illuminate\Support\Facades\Route;
use App\Models\Factura;

Route::get('/', function () {
    return view('welcome');
});

// Cargar rutas personalizadas de Filament
if (file_exists(base_path('routes/filament.php'))) {
    require base_path('routes/filament.php');
}

Route::get('/facturas/{factura}/visualizar', function (Factura $factura) {
    $factura->load([
        'cliente.persona',
        'empleado.persona',
        'pagos.metodoPago',
        'cai',
        'detalles.producto',
        'empresa',
    ]);

    return view('pdf.factura', compact('factura')); 
})->name('facturas.visualizar');

