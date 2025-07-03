<?php

namespace App\Filament\Resources\CategoriaUnidadesResource\Pages;

use App\Filament\Resources\CategoriaUnidadesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoriaUnidades extends CreateRecord
{
    protected static string $resource = CategoriaUnidadesResource::class;

    protected function getCreateButtonLabel(): string
    {
        return 'Guardar Categoria Unidad';
    }

    public function getTitle(): string
    {
        return 'Registrar Categoria Unidad'; // Cambia el título principal
    }

    public static function getCreateLabel(): string
    {
        return 'Nuevo'; // Tu texto personalizado
    }
}
