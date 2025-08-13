<?php

namespace App\Filament\Resources\TipoOrdenComprasResource\Pages;

use App\Filament\Resources\TipoOrdenComprasResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTipoOrdenCompras extends EditRecord
{
    protected static string $resource = TipoOrdenComprasResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set empresa_id and audit fields for update
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['updated_by'] = Auth::user()->id;
        return $data;
    }
}