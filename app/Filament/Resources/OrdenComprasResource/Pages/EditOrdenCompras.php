<?php

namespace App\Filament\Resources\OrdenComprasResource\Pages;

use App\Filament\Resources\OrdenComprasResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrdenComprasDetalle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditOrdenCompras extends EditRecord
{
    protected static string $resource = OrdenComprasResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Asegura que updated_by se actualice
        $data['updated_by'] = Auth::id();
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        // Obtenemos los detalles desde la sesión (modificados o nuevos)
        $detalles = session()->pull('detalles_orden', []);

        if (empty($detalles)) {
            Notification::make()
                ->title('Debe agregar al menos un producto.')
                ->danger()
                ->send();

            $this->halt();
        }

        // Elimina todos los detalles anteriores y los reemplaza con los nuevos
        $record->detalles()->delete();

        foreach ($detalles as $detalle) {
            OrdenComprasDetalle::create([
                'orden_compra_id' => $record->id,
                'producto_id' => $detalle['producto_id'],
                'cantidad' => $detalle['cantidad'],
                'precio' => $detalle['precio'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }

        return $record;
    }
}
