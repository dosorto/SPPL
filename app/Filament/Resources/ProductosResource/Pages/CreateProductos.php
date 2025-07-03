<?php

namespace App\Filament\Resources\ProductosResource\Pages;

use App\Filament\Resources\ProductosResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductos extends CreateRecord
{
    protected static string $resource = ProductosResource::class;

    protected function getFormSubmitButtonLabel(): string
    {
        return 'Guardar producto';
    }
    
    public  function getTitle(): string
    {
        return 'Nuevo producto';
    }
}
