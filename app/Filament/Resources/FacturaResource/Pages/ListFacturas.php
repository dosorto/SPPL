<?php

namespace App\Filament\Resources\FacturaResource\Pages;

use App\Filament\Resources\FacturaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacturas extends ListRecords
{
    protected static string $resource = FacturaResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            
            Actions\Action::make('generar_orden_venta')
                ->label('Generar Orden de Venta')
                
                ->url(FacturaResource::getUrl('generar-factura'))
                ->icon('heroicon-o-plus-circle'),
        ];
    
    }
}
