<?php

namespace App\Filament\Resources\TipoEmpleadoResource\Pages;

use App\Filament\Resources\TipoEmpleadoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoEmpleados extends ListRecords
{
    protected static string $resource = TipoEmpleadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
