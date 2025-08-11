<?php

namespace App\Filament\Resources\OrdenComprasInsumosResource\Pages;

use App\Filament\Resources\OrdenComprasInsumosResource;
use App\Filament\Pages\RecibirOrdenCompraInsumos;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewOrdenComprasInsumos extends ViewRecord
{
    protected static string $resource = OrdenComprasInsumosResource::class;

    protected static ?string $title = 'Ver Orden de Compra Insumos';

    public function table(Table $table): Table
    {
        return $this->getResource()::table($table);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->disabled(fn ($record) => $record->estado === 'Recibida'),
                \Filament\Actions\Action::make('recibirEnInventario')
                    ->label('Recibir en Inventario')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->hidden(fn ($record) => $record->estado === 'Recibida')
                    ->url(fn ($record): string => RecibirOrdenCompraInsumos::getUrl(['orden_id' => $record->id])),
                \Filament\Actions\Action::make('generatePdf')
                    ->label('Generar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->hidden(fn ($record) => $record->estado !== 'Recibida')
                    ->action(function ($record) {
                        $pdf = Pdf::loadView('pdf.orden-compra-insumos', [
                            'orden' => $record->load(['empresa', 'proveedor', 'detalles.producto', 'detalles.tipoOrdenCompra']),
                            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                        ]);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, "orden-compra-insumos-{$record->id}.pdf");
                    }),
                \Filament\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Orden')
                    ->modalDescription('¿Estás seguro de que quieres eliminar esta orden y todos sus detalles?')
                    ->disabled(fn ($record) => $record->estado === 'Recibida'),
            ])
                ->label('Acciones')
                ->button()
                ->outlined()
                ->dropdown(true),
        ];
    }

    protected function getFormSchema(): array
    {
        return [];
    }
}