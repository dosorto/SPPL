<?php

namespace App\Filament\Resources\ProveedoresResource\Pages;

use App\Filament\Resources\ProveedoresResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProveedores extends CreateRecord
{
    protected static string $resource = ProveedoresResource::class;

    protected function getCreateButtonLabel(): string
    {
        return 'Guardar Proveedor';
    }

    public function getTitle(): string
    {
        return 'Registrar Proveedor'; // Cambia el título principal
    }
}
