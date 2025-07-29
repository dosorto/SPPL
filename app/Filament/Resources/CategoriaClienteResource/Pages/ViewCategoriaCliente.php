<?php

namespace App\Filament\Resources\CategoriaClienteResource\Pages;

use App\Filament\Resources\CategoriaClienteResource;
use App\Models\CategoriaClienteProducto;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;

class ViewCategoriaCliente extends ViewRecord
{
    protected static string $resource = CategoriaClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getForm(string $name = 'form'): ?\Filament\Forms\Form
    {
        return $this->makeForm()
            ->schema([
                // Información básica
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Placeholder::make('nombre')
                            ->label('Nombre')
                            ->content(fn ($record) => $record->nombre),
                        Forms\Components\Placeholder::make('descripcion')
                            ->label('Descripción')
                            ->content(fn ($record) => $record->descripcion ?? 'Sin descripción'),
                        Forms\Components\Placeholder::make('activo')
                            ->label('Estado')
                            ->content(fn ($record) => $record->activo ? 'Activo' : 'Inactivo'),
                    ])
                    ->columns(3),

                // Descuentos por producto
                Forms\Components\Section::make('Descuentos por Categoría de Producto')
                    ->schema([
                        Forms\Components\View::make('components.categoria-descuentos-table')
                            ->viewData(fn () => ['record' => $this->getRecord()])
                    ])
            ])
            ->model($this->getRecord())
            ->statePath('data');
    }
}
