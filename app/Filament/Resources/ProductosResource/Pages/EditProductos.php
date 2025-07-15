<?php

// app/Filament/Resources/ProductosResource/Pages/EditProductos.php

namespace App\Filament\Resources\ProductosResource\Pages;

use App\Filament\Resources\ProductosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\ProductoFoto;

class EditProductos extends EditRecord
{
    protected static string $resource = ProductosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $fotos = $this->form->getState()['fotos'] ?? [];

        $this->record->fotosRelacion()->delete();

        foreach ($fotos as $foto) {
            ProductoFoto::create([
                'producto_id' => $this->record->id,
                'url' => is_string($foto) ? $foto : (is_array($foto) ? $foto['name'] ?? '' : ''),
            ]);
        }
    }
}
