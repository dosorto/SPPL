<?php

namespace App\Filament\Resources\CaiResource\Pages;

use App\Filament\Resources\CaiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCai extends CreateRecord
{
    protected static string $resource = CaiResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');   
    }
    protected function getSavedNotificationTitle(): string
    {
        return 'Cai Creado';
    }
}
