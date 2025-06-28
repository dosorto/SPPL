<?php

namespace App\Filament\Resources\PaisesResource\Pages;

use App\Filament\Resources\PaisesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaises extends CreateRecord
{
    protected static string $resource = PaisesResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');   
    }
    protected function getSavedNotificationTitle(): string
    {
        return 'País Creado';
    }
}
