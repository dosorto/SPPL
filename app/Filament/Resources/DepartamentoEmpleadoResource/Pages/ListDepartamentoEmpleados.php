<?php

namespace App\Filament\Resources\DepartamentoEmpleadoResource\Pages;

use App\Filament\Resources\DepartamentoEmpleadoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartamentoEmpleados extends ListRecords
{
    protected static string $resource = DepartamentoEmpleadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
