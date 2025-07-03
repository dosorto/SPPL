<?php

namespace App\Filament\Resources\TipoEmpleadoResource\Pages;

use App\Filament\Resources\TipoEmpleadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoEmpleado extends EditRecord
{
    protected static string $resource = TipoEmpleadoResource::class;

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
