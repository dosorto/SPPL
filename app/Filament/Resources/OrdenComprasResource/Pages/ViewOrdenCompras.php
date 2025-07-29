<?php

namespace App\Filament\Resources\OrdenComprasResource\Pages;

use App\Filament\Resources\OrdenComprasResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use App\Filament\Pages\RecibirOrdenCompra;
use Carbon\Carbon;

class ViewOrdenCompras extends ViewRecord
{
    protected static string $resource = OrdenComprasResource::class;

    protected static ?string $title = 'Ver Orden de Compra';

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->columns(2);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Información Básica')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Placeholder::make('tipo_orden_compra_id')
                        ->label('Tipo de Orden')
                        ->content(fn () => $this->record->tipoOrdenCompra?->nombre ?? 'N/A')
                        ->extraAttributes(['class' => 'text-lg font-semibold text-gray-800 dark:text-gray-200']),
                    Placeholder::make('proveedor_id')
                        ->label('Proveedor')
                        ->content(fn () => $this->record->proveedor?->nombre_proveedor ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600 dark:text-gray-400']),
                    Placeholder::make('empresa_id')
                        ->label('Empresa')
                        ->content(fn () => $this->record->empresa?->nombre ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600 dark:text-gray-400']),
                    Placeholder::make('fecha_realizada')
                        ->label('Fecha Realizada')
                        ->content(fn () => $this->record->fecha_realizada ? Carbon::parse($this->record->fecha_realizada)->format('d/m/Y') : 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600 dark:text-gray-400']),
                    Placeholder::make('descripcion')
                        ->label('Descripción')
                        ->content(fn () => $this->record->descripcion ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600 dark:text-gray-400']),
                    Placeholder::make('estado')
                        ->label('Estado')
                        ->content(fn () => $this->record->estado === 'Pendiente' ? 'Orden Abierta' : 'Orden en Inventario')
                        ->extraAttributes(['class' => 'text-gray-600 dark:text-gray-400']),
                ])
                ->columns(2)
                ->collapsible()
                ->extraAttributes(['class' => 'bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-600']),
            Section::make('Detalles de la Orden')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Placeholder::make('detalles')
                        ->label('')
                        ->content(function () {
                            $detalles = $this->record->detalles;
                            if ($detalles->isEmpty()) {
                                return new \Illuminate\Support\HtmlString('<p class="text-gray-600 dark:text-gray-400">No hay detalles registrados.</p>');
                            }

                            $html = '<table class="w-full table-auto border dark:border-gray-600">';
                            $html .= '<thead><tr class="bg-gray-100 dark:bg-gray-700">';
                            $html .= '<th class="px-4 py-2 text-gray-700 dark:text-gray-200">Producto</th>';
                            $html .= '<th class="px-4 py-2 text-gray-700 dark:text-gray-200">Cantidad</th>';
                            $html .= '<th class="px-4 py-2 text-gray-700 dark:text-gray-200">Precio Unitario</th>';
                            $html .= '<th class="px-4 py-2 text-gray-700 dark:text-gray-200">Total</th>';
                            $html .= '</tr></thead><tbody>';

                            foreach ($detalles as $detalle) {
                                $html .= '<tr class="dark:bg-gray-800">';
                                $html .= '<td class="border px-4 py-2 dark:border-gray-600 dark:text-gray-200">' . ($detalle->producto?->nombre ?? 'N/A') . '</td>';
                                $html .= '<td class="border px-4 py-2 dark:border-gray-600 dark:text-gray-200">' . $detalle->cantidad . '</td>';
                                $html .= '<td class="border px-4 py-2 dark:border-gray-600 dark:text-gray-200">HNL ' . number_format($detalle->precio, 2) . '</td>';
                                $html .= '<td class="border px-4 py-2 dark:border-gray-600 dark:text-gray-200">HNL ' . number_format($detalle->cantidad * $detalle->precio, 2) . '</td>';
                                $html .= '</tr>';
                            }

                            $html .= '</tbody></table>';
                            return new \Illuminate\Support\HtmlString($html);
                        })
                        ->extraAttributes(['class' => 'w-full']),
                ])
                ->collapsible()
                ->extraAttributes(['class' => 'bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-600']),
        ];
    }

    protected function getHeaderActions(): array
    {
        return $this->record->estado === 'Pendiente' ? [
            EditAction::make()
                ->label('Editar'),
            DeleteAction::make()
                ->label('Eliminar'),
            Action::make('recibirEnInventario')
                ->label('Recibir en Inventario')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('success')
                ->url(fn () => RecibirOrdenCompra::getUrl(['orden_id' => $this->record->id])),
        ] : [];
    }
}