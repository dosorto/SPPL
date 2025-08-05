<?php



// Ruta directa para descargar PDF de nómina
use App\Filament\Resources\NominaResource\Pages\ViewNomina;
use App\Filament\Pages\RecibirOrdenCompraInsumos;
Route::get('/admin/nominas/{nomina}/generar-pdf', function ($nomina) {
    $page = app(ViewNomina::class);
    $page->record = \App\Models\Nominas::findOrFail($nomina);
    return $page->generarPDF();
})->name('nominas.generar-pdf')->middleware(['web', 'auth']);

use Illuminate\Support\Facades\Route;
use App\Models\Factura;

Route::get('/', function () {
    return view('welcome');
});

// Cargar rutas personalizadas de Filament
if (file_exists(base_path('routes/filament.php'))) {
    require base_path('routes/filament.php');
}
Route::get('/admin/recibir-orden/{record}', RecibirOrdenCompraInsumos::class)
    ->middleware(['web', 'auth'])
    ->name('filament.pages.recibir-orden');

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

