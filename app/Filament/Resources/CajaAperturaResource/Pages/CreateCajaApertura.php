<?php

namespace App\Filament\Resources\CajaAperturaResource\Pages;

use App\Filament\Resources\CajaAperturaResource;
use App\Filament\Resources\FacturaResource;
use App\Models\CajaApertura; 
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth; 
use Filament\Notifications\Notification; 

class CreateCajaApertura extends CreateRecord
{
    protected static string $resource = CajaAperturaResource::class;

    
    protected function beforeCreate(): void
    {
        $cajaAbiertaExistente = CajaApertura::where('user_id', Auth::id())
                                          ->where('estado', 'ABIERTA')
                                          ->exists();

        if ($cajaAbiertaExistente) {

            Notification::make()
                ->title('Acción no permitida')
                ->body('Ya tienes una caja abierta. Debes cerrarla antes de poder crear una nueva.')
                ->danger()
                ->send();

    
            $this->halt();
        }
    }
    
    
    protected function afterCreate(): void
    {
        session(['apertura_id' => $this->record->id]);
    }

    
    protected function getRedirectUrl(): string
    {
        return FacturaResource::getUrl('generar-factura');
    }
}