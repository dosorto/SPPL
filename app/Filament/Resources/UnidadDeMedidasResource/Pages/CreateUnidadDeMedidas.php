<?php

namespace App\Filament\Resources\UnidadDeMedidasResource\Pages;

use App\Filament\Resources\UnidadDeMedidasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUnidadDeMedidas extends CreateRecord
{
    protected static string $resource = UnidadDeMedidasResource::class;

    protected function getCreateButtonLabel(): string
    {
        return 'Guardar Unidad de Medida';
    }

    public function getTitle(): string
    {
        return 'Registrar Unidad de Medida'; // Cambia el título principal
    }

    public static function getCreateLabel(): string
    {
        return 'Nuevo'; // Tu texto personalizado
    }
}
