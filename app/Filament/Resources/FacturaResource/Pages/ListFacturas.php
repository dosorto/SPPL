<?php

namespace App\Filament\Resources\FacturaResource\Pages;

use App\Filament\Resources\FacturaResource;
use App\Filament\Resources\FacturaResource\Widgets\AperturaCajaPlaceholderWidget;
use App\Filament\Resources\FacturaResource\Widgets\CajaStatsWidget;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Session;

class ListFacturas extends ListRecords
{
    protected static string $resource = FacturaResource::class;

    protected function getHeaderWidgets(): array
    {
        if (Session::has('apertura_id')) {
            // Si la caja está ABIERTA, muestra las estadísticas y botones.
            return [
                CajaStatsWidget::class,
            ];
        } else {
            // Si la caja está CERRADA, muestra el mensaje para aperturar.
            return [
                AperturaCajaPlaceholderWidget::class,
            ];
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function isTableVisible(): bool
    {
        return Session::has('apertura_id');
    }
}