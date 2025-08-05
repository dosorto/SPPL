<?php

namespace App\Filament\Resources\CajaAperturaResource\Pages;

use App\Filament\Resources\CajaAperturaResource;
use App\Models\CajaApertura;
use App\Models\Factura;
use App\Models\Pago;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Actions;

class ReporteCajaApertura extends Page
{
    protected static string $resource = CajaAperturaResource::class;
    protected static string $view = 'filament.pages.reporte-caja-apertura';
    
    public CajaApertura $record;
    public array $reporteSistema = [];
    public float $totalEnCajaEsperado = 0;
    public array $conteoUsuario = [];
    public array $diferencias = [];
    public ?string $notasCierre = null;

    public function mount(CajaApertura $record): void
    {
        $this->record = $record;
        $this->calcularDatos();
    }

    protected function calcularDatos(): void
    {

        $ventasAgrupadas = \App\Models\Pago::query()
            ->whereHas('factura', fn($q) => $q->where('apertura_id', $this->record->id))
            ->with('metodoPago')
            ->get()
            ->groupBy(fn($pago) => $pago->metodoPago->nombre)
            ->map(fn($grupo) => $grupo->sum('monto'));

        $this->reporteSistema = $ventasAgrupadas->toArray();


        $totalEfectivoSistema = $this->reporteSistema['Efectivo'] ?? 0;
        $this->totalEnCajaEsperado = $this->record->monto_inicial + $totalEfectivoSistema;


        $this->conteoUsuario = $this->record->conteo_usuario ?? [];
        $this->diferencias = $this->record->diferencias_cierre ?? [];
        $this->notasCierre = $this->record->notas_cierre;


        \Log::info('DEBUG Reporte Caja:', [
            'apertura_id' => $this->record->id,
            'reporteSistema' => $this->reporteSistema,
            'totalEnCajaEsperado' => $this->totalEnCajaEsperado,
            'conteoUsuario' => $this->conteoUsuario,
            'diferencias' => $this->diferencias
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generar_pdf')
                ->label('Generar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action('generarPDF'),
                
            Actions\Action::make('volver')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(CajaAperturaResource::getUrl('index')),
        ];
    }

    public function generarPDF()
    {

        
        $this->js('window.print()'); 
        

    }

    public function getTitle(): string
    {
        return "Reporte de Caja - " . $this->record->fecha_apertura->format('d/m/Y H:i');
    }
}