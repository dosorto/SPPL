<?php

namespace App\Filament\Resources\DetalleNominaResource\Pages;

use App\Filament\Resources\DetalleNominaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDetalleNominas extends ListRecords
{
    protected static string $resource = DetalleNominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Se eliminó la acción de crear
        ];
    }

    /**
     * Ordena los registros para mostrar los más recientes primero.
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->orderBy('created_at', 'desc');
    }
}
