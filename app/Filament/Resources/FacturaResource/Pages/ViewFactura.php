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
        // Acceder a la factura a través de $this->record
        $factura = $this->getRecord();

        // Calcular el monto pagado y el restante
        $pagado = $factura->pagos()->sum('monto');
        $restante = round(max(0, $factura->total - $pagado), 2);

        return [
            // BOTÓN 0: Para editar la factura pendiente
            Action::make('editar')
            ->label('Editar Factura')
            ->icon('heroicon-o-pencil')
            ->url(fn () => FacturaResource::getUrl('edit-pendiente', ['record' => $factura->id]))
            ->visible(fn () => $factura->estado === 'Pendiente'),

            // BOTÓN 1: Para pagar la factura
            Actions\Action::make('pagar')
                ->label('Pagar')
                ->color('success')
                ->icon('heroicon-o-currency-dollar') // Puedes elegir un icono adecuado
                ->url(FacturaResource::getUrl('registrar-pago', ['record' => $factura->id]))
                // El botón solo será visible si el estado de la factura no es 'Pagada'
                // y si el restante es mayor que 0
                ->visible(fn () => $factura->estado == 'Pendiente' && $restante > 0),

            // BOTÓN 2: Para visualizar el PDF en una nueva pestaña
            Action::make('vista_previa')
                ->label('Imprimir Factura')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url(fn () => route('facturas.visualizar', ['factura' => $this->record->id]))
                ->openUrlInNewTab(),
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
