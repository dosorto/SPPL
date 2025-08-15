<?php

namespace App\Filament\Pages;

use App\Models\CajaApertura;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use PDF;
use App\Filament\Resources\CajaAperturaResource;

class CierreCaja extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.cierre-caja';
    protected static ?string $navigationLabel = 'Cerrar Caja';
    protected static bool $shouldRegisterNavigation = false;

    public ?CajaApertura $apertura = null;
    public array $reporteSistema = [];
    public float $totalEnCajaEsperado = 0;

    public ?array $data = [];

    /**
     * Se ejecuta al cargar la página.
     */
    public function mount(): void
    {
        // Busca la apertura de caja activa para el usuario.
       $this->apertura = CajaApertura::with('user.empresa')
            ->where('user_id', Auth::id())
            ->where('estado', 'ABIERTA')
            ->first();
        if (!$this->apertura) {
            $this->redirect(route('filament.admin.pages.apertura-caja'));
            return;
        }

        // Obtiene las ventas agrupadas por método de pago.
        $ventasAgrupadas = \App\Models\Pago::query()
            ->whereHas('factura', fn($q) => $q->where('apertura_id', $this->apertura->id))
            ->with('metodoPago')
            ->get()
            ->groupBy(fn($pago) => $pago->metodoPago->nombre)
            ->map(fn($grupo) => $grupo->sum('monto'));

        $this->reporteSistema = $ventasAgrupadas->toArray();

        // Calcula el total esperado en efectivo (incluyendo el monto inicial).
        $totalEfectivoSistema = $this->reporteSistema['Efectivo'] ?? 0;
        $this->totalEnCajaEsperado = $this->apertura->monto_inicial + $totalEfectivoSistema;
        
        // Inicializa el formulario con datos vacíos.
        $this->form->fill();
    }

    /**
     * Define la estructura del formulario de Filament.
     */
    public function form(Form $form): Form
    {
        $formSchema = [];

        // Genera los campos de texto dinámicamente para cada método de pago.
        foreach ($this->reporteSistema as $metodo => $monto) {
            $formSchema[] = TextInput::make("conteo.{$metodo}")
                ->label($metodo)
                ->numeric()
                ->prefix('L')
                ->placeholder('0.00')
                ->required()
                ->live(onBlur: true) // Actualiza el campo en tiempo real al perder el foco.
                ->hint(function ($get) use ($metodo, $monto) {
                    // Obtiene el valor que el usuario está escribiendo.
                    $contado = floatval($get("conteo.{$metodo}") ?? 0);
                    
                    // Determina el monto de sistema a comparar.
                    $montoSistema = ($metodo === 'Efectivo') ? $this->totalEnCajaEsperado : $monto;
                    
                    // Calcula la diferencia.
                    $diferencia = $contado - $montoSistema;
                    
                    // Renderiza la vista del componente de diferencia, pasándole los datos.
                    return View::make('filament.forms.components.diferencia-display', [
                        'sistema' => $montoSistema,
                        'diferencia' => $diferencia,
                    ]);
                });
        }

        // Añade el campo de notas al final.
        $formSchema[] = Textarea::make('notas_cierre')
            ->label('Notas o Descripción General del Cierre')
            ->rows(4)
            ->placeholder('Ej: La diferencia en tarjeta es por un cobro duplicado...');

        return $form->schema($formSchema)->statePath('data');
    }

    /**
     * Define las acciones de la página, como los botones de confirmar y generar PDF.
     */
    protected function getActions(): array
    {
        return [
            

            // Acción para confirmar el cierre (ahora también genera el PDF)
            Action::make('confirmarCierre')
                ->label('Confirmar y Cerrar Caja')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $formData = $this->form->getState();
                    $conteoUsuario = $formData['conteo'] ?? [];
                    $diferencias = [];

                    foreach ($this->reporteSistema as $metodo => $monto) {
                        $montoContado = floatval($conteoUsuario[$metodo] ?? 0);
                        $montoSistema = ($metodo === 'Efectivo') ? $this->totalEnCajaEsperado : $monto;
                        $diferencias[$metodo] = $montoContado - $montoSistema;
                    }
                    
                    // Actualiza el registro de la apertura en la base de datos
                    $this->apertura->update([
                        'estado' => 'CERRADA',
                        'fecha_cierre' => now(),
                        'monto_final_calculado' => $this->totalEnCajaEsperado, 
                        'conteo_usuario' => $conteoUsuario, 
                        'diferencias_cierre' => $diferencias, 
                        'notas_cierre' => $formData['notas_cierre'] ?? null,
                    ]);

                    session()->forget('apertura_id');
                    Notification::make()->title('Caja cerrada exitosamente')->success()->send();
                    
                    
                    // Generar el PDF
                $pdfResponse = $this->generarReportePdfStream();
                
                // Redirigir al resource CajaApertura
               $this->redirect(CajaAperturaResource::getUrl('index'));
                
                return $pdfResponse;
                }),
        ];
    }

    
    private function generarReportePdfStream()
    {
        if (!$this->apertura) {
            Notification::make()
                ->title('Error')
                ->body('No hay una caja abierta para generar el reporte.')
                ->danger()
                ->send();
            return null;
        }

        $formData = $this->form->getState();
        $conteoUsuario = $formData['conteo'] ?? [];

        $diferencias = [];
        foreach ($this->reporteSistema as $metodo => $monto) {
            $montoContado = floatval($conteoUsuario[$metodo] ?? 0);
            $montoSistema = ($metodo === 'Efectivo') ? $this->totalEnCajaEsperado : $monto;
            $diferencias[$metodo] = [
                'contado' => $montoContado,
                'sistema' => $montoSistema,
                'diferencia' => $montoContado - $montoSistema
            ];
        }

        $data = [
            'apertura' => $this->apertura,
            'reporteSistema' => $this->reporteSistema,
            'totalEnCajaEsperado' => $this->totalEnCajaEsperado,
            'conteoUsuario' => $conteoUsuario,
            'diferencias' => $diferencias,
            'notasCierre' => $formData['notas_cierre'] ?? '',
        ];

        try {
            $pdf = PDF::loadView('pdf.cierre-caja', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'cierre-caja-' . $this->apertura->id . '-' . now()->format('Y-m-d-H-i') . '.pdf';
            
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
}