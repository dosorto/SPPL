<?php

namespace App\Filament\Resources\DeduccionesResource\Pages;

use App\Filament\Resources\DeduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateDeducciones extends CreateRecord
{
    protected static string $resource = DeduccionesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
