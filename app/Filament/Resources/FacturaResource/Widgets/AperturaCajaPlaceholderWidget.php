<?php

namespace App\Filament\Resources\FacturaResource\Widgets;

use App\Filament\Resources\CajaAperturaResource;
use Filament\Actions\Action;
use Filament\Widgets\Widget;

class AperturaCajaPlaceholderWidget extends Widget
{
    protected static string $view = 'filament.resources.factura-resource.widgets.apertura-caja-placeholder-widget';

    public function getAperturarCajaAction(): Action
    {
        return Action::make('aperturarCaja')
            ->label('Aperturar Caja para Empezar')->icon('heroicon-o-key')
            ->color('primary')->url(CajaAperturaResource::getUrl('create'));
    }
}