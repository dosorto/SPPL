<?php

namespace App\Filament\Resources\EmpleadoPersepcionesResource\Pages;

use App\Filament\Resources\EmpleadoPersepcionesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmpleadoPersepciones extends ListRecords
{
    protected static string $resource = EmpleadoPersepcionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
