<?php

namespace App\Filament\Resources\FacturaCajaResource\Pages;

use App\Filament\Resources\CajaAperturaResource;
use App\Filament\Resources\FacturaCajaResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Session;

class ListFacturaCajas extends ListRecords
{
    protected static string $resource = FacturaCajaResource::class;

    /**
     * Oculta el botón por defecto de "Crear" en la cabecera.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
    
    /**
     * Define las acciones a mostrar cuando la tabla no tiene registros.
     */
    protected function getEmptyStateActions(): array
    {
        // Si NO hay una sesión de caja activa, muestra el botón para aperturar.
        if (!Session::has('apertura_id')) {
            return [
                Action::make('aperturar_caja')
                    ->label('Aperturar Caja')
                    ->icon('heroicon-o-key')
                    ->url(CajaAperturaResource::getUrl('create')),
            ];
        }

        return [];
    }

    /**
     * Define el título a mostrar cuando la tabla está vacía.
     */
    protected function getEmptyStateHeading(): ?string
    {
        if (!Session::has('apertura_id')) {
            return 'No hay una caja aperturada';
        }

        return 'Sin facturas todavía';
    }

    /**
     * Define la descripción a mostrar cuando la tabla está vacía.
     */
    protected function getEmptyStateDescription(): ?string
    {
        if (!Session::has('apertura_id')) {
            return 'Por favor, aperture una caja para empezar a facturar.';
        }

        return 'Cuando se genere la primera factura, aparecerá aquí.';
    }

    /**
     * Controla si la tabla de facturas debe ser visible.
     */
    protected function isTableVisible(): bool
    {
        // La tabla solo se muestra si existe una sesión de caja activa.
        return Session::has('apertura_id');
    }
}