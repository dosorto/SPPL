<?php

namespace App\Filament\Resources\OrdenComprasResource\Pages;

use App\Filament\Resources\OrdenComprasResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateOrdenCompras extends CreateRecord
{
    protected static string $resource = OrdenComprasResource::class;

    protected function handleRecordCreation(array $data): Model
{
    // Validación de campos básicos
    $camposBasicosCompletos = $data['tipo_orden_compra_id'] &&
                              $data['proveedor_id'] &&
                              $data['empresa_id'] &&
                              $data['fecha_realizada'];

    $detalles = session()->pull('detalles_orden', []);

    if (!$camposBasicosCompletos || empty($detalles)) {
        Notification::make()
            ->title('Error al crear orden')
            ->body('Debe completar la información básica y agregar al menos un producto.')
            ->danger()
            ->send();

        $this->halt(); // Detiene el flujo de creación
    }

    // Crear la orden
    $orden = OrdenCompras::create([
        'tipo_orden_compra_id' => $data['tipo_orden_compra_id'],
        'proveedor_id' => $data['proveedor_id'],
        'empresa_id' => $data['empresa_id'],
        'fecha_realizada' => $data['fecha_realizada'],
        'descripcion' => $data['descripcion'] ?? null,
        'estado' => 'Pendiente',
        'created_by' => Auth::id(),
        'updated_by' => Auth::id(),
    ]);

    foreach ($detalles as $detalle) {
        OrdenComprasDetalle::create([
            'orden_compra_id' => $orden->id,
            'producto_id' => $detalle['producto_id'],
            'cantidad' => $detalle['cantidad'],
            'precio' => $detalle['precio'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
    }

    return $orden;
}

}