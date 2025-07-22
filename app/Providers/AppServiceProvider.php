<?php

namespace App\Providers;

use App\Models\DetalleNominas;
use App\Observers\DetalleNominasObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar el observer para DetalleNominas
        \App\Models\DetalleNominas::observe(\App\Observers\DetalleNominasObserver::class);
    }
}
