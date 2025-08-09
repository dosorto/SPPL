<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CajaAperturaResource; 
use App\Filament\Resources\FacturaResource;
use App\Models\CajaApertura;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Pages\Page;

class AperturaCaja extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Aperturar Caja';
    protected static ?string $navigationGroup = 'Ventas';
    protected static string $view = 'filament.pages.apertura-caja';
    protected static bool $shouldRegisterNavigation = false;

    public ?CajaApertura $aperturaActiva = null;

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user || !$user->empresa_id) {
            return;
        }

        $this->aperturaActiva = CajaApertura::where('user_id', Auth::id())
                                          ->where('estado', 'ABIERTA')
                                          ->where('empresa_id', $user->empresa_id)
                                          ->first();
        
   
        if ($this->aperturaActiva) {
            session(['apertura_id' => $this->aperturaActiva->id]);
        }
    }


    public function getAperturarCajaAction(): Action
    {
        return Action::make('aperturarCaja')
            ->label('Aperturar Caja')
            ->url(fn (): string => CajaAperturaResource::getUrl('create')) 
            ->visible(!$this->aperturaActiva && Auth::user()?->empresa_id);
    }
    

    public function getIrAFacturarAction(): Action
    {
        return Action::make('irAFacturar')
            ->label('Ir a Facturar')
            ->color('success')
            ->url(fn (): string => FacturaResource::getUrl('generar-factura'))
            ->visible((bool)$this->aperturaActiva);
    }

    
}