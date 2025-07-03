<?php

namespace App\Filament\Resources\TipoEmpleadoResource\Pages;

use App\Filament\Resources\TipoEmpleadoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoEmpleado extends CreateRecord
{
    protected static string $resource = TipoEmpleadoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
