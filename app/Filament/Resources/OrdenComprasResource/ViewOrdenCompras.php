<?php

namespace App\Filament\Resources\OrdenComprasResource\Pages;

use App\Filament\Resources\OrdenComprasResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
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
                        ->extraAttributes(['class' => 'text-lg font-semibold text-gray-800']),
                    Placeholder::make('proveedor_id')
                        ->label('Proveedor')
                        ->content(fn () => $this->record->proveedor?->nombre_proveedor ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),
                    Placeholder::make('empresa_id')
                        ->label('Empresa')
                        ->content(fn () => $this->record->empresa?->nombre ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),
                    Placeholder::make('fecha_realizada')
                        ->label('Fecha Realizada')
                        ->content(fn () => $this->record->fecha_realizada ? Carbon::parse($this->record->fecha_realizada)->format('d/m/Y') : 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),
                    Placeholder::make('descripcion')
                        ->label('Descripción')
                        ->content(fn () => $this->record->descripcion ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),
                ])
                ->columns(2)
                ->collapsible()
                ->extraAttributes(['class' => 'bg-white p-6 rounded-xl shadow-md border border-gray-200']),
        ];
    }
}