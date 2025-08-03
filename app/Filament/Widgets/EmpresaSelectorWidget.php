<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

// Este widget ha sido deshabilitado para evitar duplicación con el render hook
class EmpresaSelectorWidget extends Widget
{
    protected static string $view = 'filament.widgets.empresa-selector-widget';
    
    // Deshabilitar este widget por completo
    public static function canView(): bool
    {
        return false;
    }
}
