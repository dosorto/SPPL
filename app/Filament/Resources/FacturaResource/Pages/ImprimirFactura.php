<?php

namespace App\Filament\Resources\FacturaResource\Pages;

use App\Filament\Resources\FacturaResource;
use Filament\Resources\Pages\Page;
use App\Models\Factura;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ImprimirFactura extends Page
{
    protected static string $resource = FacturaResource::class;

    protected static string $view = 'filament.resources.factura-resource.pages.imprimir-factura';
    public ?Factura $record = null;

    public function mount(int | string $record): void
    {
        $this->record = Factura::with([
            'cliente.persona',
            'empleado.persona',
            'pagos.metodoPago',
            'cai',
            'empresa',
            'detalles.producto', // Corregido: 'producto.producto' era probablemente un error
        ])->findOrFail($record);

        // Carga la vista para generar el PDF
        $pdf = Pdf::loadView(static::$view, ['factura' => $this->record]);

        // Crea la respuesta
        $response = response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="factura_' . $this->record->id . '.pdf"',
        ]);

        // Envía la respuesta completa (headers y contenido) y detiene el script
        $response->send();
        exit;
    }
}
