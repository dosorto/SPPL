<?php

namespace App\Providers;

use App\Models\DetalleNominas;
use App\Observers\DetalleNominasObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

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
        
        // Registrar alias para Excel
        if (class_exists('Maatwebsite\\Excel\\ExcelServiceProvider')) {
            $this->app->alias('Excel', 'Maatwebsite\\Excel\\Facades\\Excel');
        }
    }
}
