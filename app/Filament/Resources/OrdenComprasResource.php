<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenComprasResource\Pages;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class OrdenComprasResource extends Resource
{
    protected static ?string $model = OrdenCompras::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?string $navigationLabel = 'Órdenes de Compra';
    protected static ?string $pluralModelLabel = 'Órdenes de Compra';
    protected static ?string $modelLabel = 'Orden de Compra';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Datos principales')
                        ->schema([
                            Forms\Components\Select::make('tipo_orden_compra_id')
                                ->label('Tipo de Orden')
                                ->relationship('tipoOrdenCompra', 'nombre')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->optionsLimit(100),
                            Forms\Components\Select::make('proveedor_id')
                                ->label('Proveedor')
                                ->relationship('proveedor', 'nombre_proveedor')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->optionsLimit(100),
                            Forms\Components\Select::make('empresa_id')
                                ->label('Empresa')
                                ->relationship('empresa', 'nombre')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->optionsLimit(100),
                            Forms\Components\DatePicker::make('fecha_realizada')
                                ->label('Fecha Realizada')
                                ->required()
                                ->default(now()),
                            Forms\Components\Hidden::make('created_by')
                                ->default(Auth::id()),
                            Forms\Components\Hidden::make('updated_by')
                                ->default(Auth::id()),
                        ]),
                    Forms\Components\Wizard\Step::make('Detalles de la Orden')
                        ->schema([
                            Forms\Components\Repeater::make('detalles')
                                ->label('Productos')
                                ->relationship('detalles')
                                ->schema([
                                    Forms\Components\Select::make('producto_id')
                                        ->label('Producto')
                                        ->relationship('producto', 'nombre')
                                        ->searchable()
                                        ->required()
                                        ->preload()
                                        ->optionsLimit(100),
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
                                    Forms\Components\Hidden::make('created_by')
                                        ->default(Auth::id()),
                                    Forms\Components\Hidden::make('updated_by')
                                        ->default(Auth::id()),
                                ])
                                ->columns(3)
                                ->required(),
                        ]),
                ])
                ->statePath('data'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipoOrdenCompra.nombre')
                    ->label('Tipo Orden')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('proveedor.nombre_proveedor')
                    ->label('Proveedor')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_realizada')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detalles_count')
                    ->label('Productos')
                    ->counts('detalles')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Ver'),
                    Tables\Actions\EditAction::make()->label('Editar'),
                    Tables\Actions\DeleteAction::make()->label('Eliminar'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\OrdenComprasResource\RelationManagers\DetallesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenCompras::route('/'),
            'create' => Pages\CreateOrdenCompras::route('/create'),
            'edit' => Pages\EditOrdenCompras::route('/{record}/edit'),
            'view' => Pages\ViewOrdenCompras::route('/{record}/detalles'),
        ];
    }
}
