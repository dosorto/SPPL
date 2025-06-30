<?php

namespace App\Filament\Resources\DepartamentoEmpleadoResource\Pages;

use App\Filament\Resources\DepartamentoEmpleadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartamentoEmpleado extends EditRecord
{
    protected static string $resource = DepartamentoEmpleadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
