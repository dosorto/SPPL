<?php

namespace App\Filament\Pages;

use App\Models\CajaApertura;
use App\Models\Factura;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Pago;


class CierreCaja extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.cierre-caja';
    protected static ?string $navigationLabel = 'Cerrar Caja';

    // Oculta esta página del menú principal. Solo se accederá a través de un botón.
    protected static bool $shouldRegisterNavigation = false;

    public ?CajaApertura $apertura = null;
    public array $reporte = [];
    public float $totalVendido = 0;
    public float $totalEfectivo = 0;
    public float $totalEnCajaEsperado = 0;

    // El método mount se ejecuta al cargar la página.
    public function mount(): void
    {
        // Buscamos la apertura de caja activa para el usuario.
        $this->apertura = CajaApertura::where('user_id', Auth::id())
            ->where('estado', 'ABIERTA')
            ->first();

        // Si por alguna razón no hay una caja abierta, lo sacamos de aquí.
        if (!$this->apertura) {
            $this->redirect(route('filament.admin.pages.apertura-caja'));
            return;
        }

  
    $ventasAgrupadas = \App\Models\Pago::query()
        ->whereHas('factura', function ($query) {
            $query->where('apertura_id', $this->apertura->id);
        })
        
        ->with('metodoPago')
        ->get()
    
        ->groupBy(function($pago) {
            return $pago->metodoPago->nombre; 
        })
        
        ->map(function($grupo) {
            return $grupo->sum('monto');
        });
    


    foreach ($ventasAgrupadas as $metodo => $total) {
        $this->reporte[$metodo] = $total;
        $this->totalVendido += $total;


        if (strtolower(trim($metodo)) === 'efectivo') {
            $this->totalEfectivo = $total;
        }
    }
    
    $this->totalEnCajaEsperado = $this->apertura->monto_inicial + $this->totalEfectivo;
    }

    // Acción para el botón de confirmación.
    protected function getActions(): array
    {
        return [
            Action::make('confirmarCierre')
                ->label('Confirmar y Cerrar Caja')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    
                    
                    $this->apertura->update([
                        'estado' => 'CERRADA',
                        'fecha_cierre' => now(),
                        'monto_final_calculado' => $this->totalVendido,
                    ]);

                    
                    session()->forget('apertura_id');

                    Notification::make()
                        ->title('Caja cerrada exitosamente')
                        ->success()
                        ->send();

                    $this->redirect(route('filament.admin.pages.apertura-caja'));
                }),
        ];
    }
}