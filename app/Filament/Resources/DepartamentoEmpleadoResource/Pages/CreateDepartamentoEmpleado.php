<?php

namespace App\Filament\Resources\DepartamentoEmpleadoResource\Pages;

use App\Filament\Resources\DepartamentoEmpleadoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartamentoEmpleado extends CreateRecord
{
    protected static string $resource = DepartamentoEmpleadoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
