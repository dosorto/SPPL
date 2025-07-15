<?php

namespace App\Filament\Resources\EmpleadoDeduccionesResource\Pages;

use App\Filament\Resources\EmpleadoDeduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmpleadoDeducciones extends ListRecords
{
    protected static string $resource = EmpleadoDeduccionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
