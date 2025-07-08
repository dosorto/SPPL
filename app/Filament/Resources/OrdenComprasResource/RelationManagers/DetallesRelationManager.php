<?php

namespace App\Filament\Resources\OrdenComprasResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $title = 'Detalles de la Orden';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('precio')
                    ->label('Precio Unitario')
                    ->required()
                    ->numeric()
                    ->prefix('HNL'),
            ]);
    }

    public function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn ($query) => $query->with('producto'))
        ->columns([
            Tables\Columns\TextColumn::make('producto.nombre')
                ->label('Producto')
                ->sortable(),
            Tables\Columns\TextColumn::make('cantidad')
                ->label('Cantidad')
                ->sortable(),
            Tables\Columns\TextColumn::make('precio')
                ->label('Precio Unitario')
                ->money('HNL', true)
                ->sortable(),
        ])
        ->actions([
            Tables\Actions\EditAction::make()->label('Editar'),
            Tables\Actions\DeleteAction::make()->label('Eliminar'),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
        ]);
}
}
