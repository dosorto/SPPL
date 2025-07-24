<?php

namespace App\Filament\Resources\PercepcionesResource\Pages;

use App\Filament\Resources\PercepcionesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePercepciones extends CreateRecord
{
    protected static string $resource = PercepcionesResource::class;

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
