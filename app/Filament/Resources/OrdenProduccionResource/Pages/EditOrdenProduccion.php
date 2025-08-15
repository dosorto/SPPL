<?php

namespace App\Filament\Resources\OrdenProduccionResource\Pages;

use App\Models\Rendimiento;
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

        // Descontar stock
        foreach ($record->insumos as $ins) {
            $porDescontar = $ins->cantidad_utilizada;
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

        // Calcular valores para el rendimiento
        if (!$record->rendimiento) {
            $insumos = $record->insumos;
            $empresaId = $record->empresa_id;
            $cantidad_mp = 0;
            $precio_mp = 0;
            $precio_otros_mp = 0;
            $margen_ganancia = 0; // Puedes ajustar esto según tu lógica

            // Suponiendo que el primer insumo es la materia prima principal
            if (count($insumos) > 0) {
                $mp = $insumos[0];
                $cantidad_mp = $mp->cantidad_utilizada;
                $inventario_mp = app('App\\Models\\InventarioInsumos')::where('producto_id', $mp->insumo_id)
                    ->where('empresa_id', $empresaId)
                    ->first();
                $precio_mp = $inventario_mp ? ($mp->cantidad_utilizada * $inventario_mp->precio_costo) : 0;
            }

            // Otros insumos
            if (count($insumos) > 1) {
                for ($i = 1; $i < count($insumos); $i++) {
                    $ins = $insumos[$i];
                    $inventario = app('App\\Models\\InventarioInsumos')::where('producto_id', $ins->insumo_id)
                        ->where('empresa_id', $empresaId)
                        ->first();
                    $precio_otros_mp += $inventario ? ($ins->cantidad_utilizada * $inventario->precio_costo) : 0;
                }
            }

            // Puedes definir el margen de ganancia aquí si tienes una lógica
            $margen_ganancia = 20; // Ejemplo: 20%

            $rendimiento = app('App\Models\Rendimiento')::create([
                'orden_produccion_id' => $record->id,
                'cantidad_mp' => $cantidad_mp,
                'precio_mp' => $precio_mp,
                'precio_otros_mp' => $precio_otros_mp,
                'margen_ganancia' => $margen_ganancia,
                'created_by' => auth()->id(),
            ]);

            // Crear productos finales asociados al rendimiento
            // Ejemplo: solo 1 producto final igual al producto de la orden
            app('App\Models\ProductoProducciones')::create([
                'rendimientos_id' => $rendimiento->id,
                'producto_id' => $record->producto_id,
                'cantidad' => $record->cantidad,
                'unidades_id' => $record->unidad_de_medida_id,
                'estado' => 'Finalizado',
                'created_by' => auth()->id(),
            ]);
        }

        Notification::make()
            ->title('Orden finalizada correctamente y rendimiento generado')
            ->success()
            ->send();
    }),
        ];
    }
}