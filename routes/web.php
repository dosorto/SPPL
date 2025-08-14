<?php

use App\Models\Factura;
use App\Mail\ContactFormMail;
use App\Mail\MembershipRequestMail; // Importa la nueva clase de correo
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Filament\Pages\RecibirOrdenCompraInsumos;
use App\Filament\Resources\NominaResource\Pages\ViewNomina;

// Ruta directa para descargar PDF de nómina
Route::get('/admin/nominas/{nomina}/generar-pdf', function ($nomina) {
    $page = app(ViewNomina::class);
    $page->record = \App\Models\Nominas::findOrFail($nomina);
    return $page->generarPDF();
})->name('nominas.generar-pdf')->middleware(['web', 'auth']);

// Rutas de Filament y otras personalizadas
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

// --- RUTAS DE MI SITIO WEB ---

// Ruta para la página principal 'welcome.blade.php'
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Rutas para las páginas de membresía (GET para ver la página)
Route::get('/membresia-esencial', function () {
    return view('membership-essential');
})->name('membresia.esencial');

Route::get('/membresia-avanzada', function () {
    return view('membership-advanced');
})->name('membresia.avanzada');

Route::get('/membresia-premium', function () {
    return view('membership-premium');
})->name('membresia.premium');

// Ruta para la página de contacto (GET para ver el formulario)
Route::get('/contacto', function () {
    return view('contact');
})->name('contacto');

// Ruta para procesar el formulario de contacto (POST)
Route::post('/contacto', function (Request $request) {
    $request->validate([
        'name' => 'required',
        'email' => 'required|email',
        'message' => 'required',
    ]);
    Mail::to('jadehenterprise@gmail.com')->send(new ContactFormMail($request->all()));
    return redirect()->back()->with('success', '¡Tu mensaje ha sido enviado exitosamente!');
})->name('contacto.enviar');

// Nueva ruta para procesar las solicitudes de membresía (POST)
Route::post('/membresia-solicitud', function (Request $request) {
    // Valida los datos del formulario
    $request->validate([
        'name' => 'required',
        'email' => 'required|email',
        'plan_name' => 'required', // Asegura que el plan sea enviado
    ]);

    // Envía el correo electrónico con los datos de la solicitud
    Mail::to('jadehenterprise@gmail.com')->send(new MembershipRequestMail($request->all()));

    return redirect()->route('welcome')->with('success', '¡Tu solicitud ha sido enviada exitosamente!');
})->name('membresia.solicitud');