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
                ->action(function ($record) {
                    $errores = [];

                    foreach ($record->insumos as $ins) {
                        // SUMA stock del insumo en TODAS las empresas (temporal)
                        $disponible = InventarioInsumos::where('producto_id', $ins->insumo_id)
                            ->sum('cantidad');

                        if ($disponible < $ins->cantidad_utilizada) {
                            $nombre = $ins->insumo->nombre ?? ('ID ' . $ins->insumo_id);
                            $errores[] = "Insumo '{$nombre}' insuficiente. Disponible: {$disponible}, requerido: {$ins->cantidad_utilizada}";
                        }
                    }

                    if ($errores) {
                        Notification::make()
                            ->title('No se puede finalizar la orden')
                            ->body(implode("\n", $errores))
                            ->danger()
                            ->send();
                        return;
                    }

                    // (Opcional) Descontar stock de inventario_insumos de forma global
                    foreach ($record->insumos as $ins) {
                        $porDescontar = $ins->cantidad_utilizada;

                        // primero intenta descontar de la empresa de la orden; si no alcanza, del resto
                        $lotes = InventarioInsumos::where('producto_id', $ins->insumo_id)
                            ->orderByRaw('empresa_id = ? desc', [$record->empresa_id])
                            ->get();

                        foreach ($lotes as $lote) {
                            if ($porDescontar <= 0) break;
                            $usa = min($porDescontar, $lote->cantidad);
                            if ($usa <= 0) continue;

                            $lote->cantidad -= $usa;
                            $lote->save();

                            $porDescontar -= $usa;
                        }
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