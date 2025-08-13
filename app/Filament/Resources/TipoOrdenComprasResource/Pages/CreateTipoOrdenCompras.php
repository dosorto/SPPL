<?php

namespace App\Filament\Resources\TipoOrdenComprasResource\Pages;

use App\Filament\Resources\TipoOrdenComprasResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTipoOrdenCompras extends CreateRecord
{
    protected static string $resource = TipoOrdenComprasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set empresa_id and audit fields for create
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;
        return $data;
    }
}