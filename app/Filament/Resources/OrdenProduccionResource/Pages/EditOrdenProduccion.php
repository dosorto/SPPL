<?php

namespace App\Filament\Resources\OrdenProduccionResource\Pages;

use App\Filament\Resources\OrdenProduccionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\InventarioInsumos;

class EditOrdenProduccion extends EditRecord
{
    protected static string $resource = OrdenProduccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('finalizar')
                ->label('Finalizar Producción')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->estado !== 'Finalizada')
                ->action(function ($record, $data) {
                    $errores = [];
                    foreach ($record->insumos as $insumo) {
                        $inv = InventarioInsumos::where('producto_id', $insumo->insumo_id)
                            ->where('empresa_id', $record->empresa_id)
                            ->first();
                        if (!$inv || $inv->cantidad < $insumo->cantidad_utilizada) {
                            $nombre = $insumo->insumo->nombre ?? ('ID ' . $insumo->insumo_id);
                            $errores[] = "Insumo '$nombre' insuficiente. Disponible: " . ($inv->cantidad ?? 0) . ", requerido: $insumo->cantidad_utilizada";
                        }
                    }
                    if (count($errores)) {
                        Notification::make()
                            ->title('No se puede finalizar la orden')
                            ->body(implode("\n", $errores))
                            ->danger()
                            ->send();
                        return;
                    }
                    $record->estado = 'Finalizada';
                    $record->save();
                    Notification::make()
                        ->title('Orden finalizada correctamente')
                        ->success()
                        ->send();
                }),
        ];
    }
}
