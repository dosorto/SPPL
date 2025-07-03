<?php

namespace App\Filament\Resources\TipoOrdenComprasResource\Pages;

use App\Filament\Resources\TipoOrdenComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoOrdenCompras extends CreateRecord
{
    protected static string $resource = TipoOrdenComprasResource::class;

    protected function getCreateButtonLabel(): string
    {
        return 'Guardar Tipo de Compra';
    }

    public function getTitle(): string
    {
        return 'Registrar Tipo de Compra'; // Cambia el título principal
    }

    public static function getCreateLabel(): string
    {
        return 'Nuevo'; // Tu texto personalizado
    }
}
