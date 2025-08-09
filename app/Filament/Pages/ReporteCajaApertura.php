<?php

namespace App\Filament\Resources\CajaAperturaResource\Pages;

use App\Filament\Resources\CajaAperturaResource;
use App\Models\CajaApertura;
use App\Models\Factura;
use App\Models\Pago;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Actions;
use Filament\Notifications\Notification;
use PDF; // Asegúrate de tener este import

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
    public float $totalDiferencias = 0;
    public string $estadoCierre = '';
    public string $estadoAprobacion = '';

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
        $this->notasCierre = $this->record->notas_cierre;

        
        $this->calcularDiferencias();
    }

    protected function calcularDiferencias(): void
    {
        if (!empty($this->conteoUsuario)) {
            
            $this->diferencias = $this->record->diferencias_cierre ?? [];
        } else {
            
            foreach ($this->reporteSistema as $metodo => $monto) {
                $montoSistema = ($metodo === 'Efectivo') ? $this->totalEnCajaEsperado : $monto;
                $this->diferencias[$metodo] = [
                    'sistema' => $montoSistema,
                    'contado' => 0,
                    'diferencia' => 0 - $montoSistema
                ];
            }
        }

        
        foreach ($this->diferencias as $metodo => $valor) {
            if (is_numeric($valor)) {
                
                $montoSistema = ($metodo === 'Efectivo') ? $this->totalEnCajaEsperado : ($this->reporteSistema[$metodo] ?? 0);
                $montoContado = $montoSistema + $valor;
                
                $this->diferencias[$metodo] = [
                    'sistema' => $montoSistema,
                    'contado' => $montoContado,
                    'diferencia' => $valor
                ];
            }
        }

        
        $this->totalDiferencias = collect($this->diferencias)->sum('diferencia');
        
       
        $this->estadoCierre = abs($this->totalDiferencias) <= 1 ? 'Correcto' : 
                             ($this->totalDiferencias > 0 ? 'Sobrante' : 'Faltante');
        
        
        $this->estadoAprobacion = abs($this->totalDiferencias) <= 1 ? 'Aprobado' : 'Requiere Revisión';
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
        try {
            
            $data = [
                'apertura' => $this->record,
                'reporteSistema' => $this->reporteSistema,
                'totalEnCajaEsperado' => $this->totalEnCajaEsperado,
                'conteoUsuario' => $this->conteoUsuario,
                'diferencias' => $this->diferencias,
                'notasCierre' => $this->notasCierre ?? '',
            ];

            
            $pdf = PDF::loadView('pdf.cierre-caja', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'reporte-caja-' . $this->record->id . '-' . now()->format('Y-m-d-H-i') . '.pdf';
            
            // Descargar el PDF
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al generar PDF')
                ->body('Hubo un problema al generar el reporte: ' . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }
    }

    public function getTitle(): string
    {
        return "Reporte de Caja #" . $this->record->id . " - " . $this->record->fecha_apertura->format('d/m/Y H:i');
    }
}