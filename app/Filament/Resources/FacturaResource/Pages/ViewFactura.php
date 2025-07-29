<?php

namespace App\Filament\Resources\FacturaResource\Pages;

use App\Filament\Resources\FacturaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Factura;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;


class ViewFactura extends ViewRecord
{
    protected static string $resource = FacturaResource::class;
    protected static string $view = 'filament.resources.factura-resource.pages.view-factura';

    protected function getHeaderActions(): array
    {
        return [
            // BOTÓN 1: Para visualizar el PDF en una nueva pestaña
            
            Action::make('vista_previa')
                ->label('Imprimir Factura')
                ->icon('heroicon-o-printer') 
                ->color('warning')
                ->url(fn () => route('facturas.visualizar', ['factura' => $this->record->id]))
                ->openUrlInNewTab(),



            // BOTÓN 2: Para descargar el PDF directamente
            
        ];
    }
    

    protected function resolveRecord(int | string $key): Model
    {
        return Factura::with([
            'cliente.persona',
            'empleado.persona',
            'pagos.metodoPago',
            'cai',
            'detalles.producto',
        ])->findOrFail($key);
    }
}
