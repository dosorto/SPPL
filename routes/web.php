<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Cargar rutas personalizadas de Filament
if (file_exists(base_path('routes/filament.php'))) {
    require base_path('routes/filament.php');
}
