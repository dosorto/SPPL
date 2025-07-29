<?php

namespace App\Filament\Resources\OrdenComprasResource\Pages;

use App\Filament\Resources\OrdenComprasResource;
use Filament\Resources\Pages\EditRecord;
use App\Models\OrdenComprasDetalle;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditOrdenCompras extends EditRecord
{
    protected static string $resource = OrdenComprasResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();
        return $data;
    }

    protected function afterSave(): void
    {
        $detalles = session()->pull('detalles_orden', []);

        if (!empty($detalles)) {
            // Eliminar detalles existentes y volver a crearlos
            $this->record->detalles()->delete();

            foreach ($detalles as $detalle) {
                OrdenComprasDetalle::create([
                    'orden_compra_id' => $this->record->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $detalle['precio'],
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }

        Notification::make()
            ->title('Orden actualizada')
            ->success()
            ->send();
    }
}
