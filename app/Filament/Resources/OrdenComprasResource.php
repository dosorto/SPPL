<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenComprasResource\Pages;
use App\Models\OrdenCompras;
use App\Models\Productos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                                ->searchable(),

                            Forms\Components\Select::make('proveedor_id')
                                ->label('Proveedor')
                                ->relationship('proveedor', 'nombre_proveedor')
                                ->required()
                                ->searchable(),

                            Forms\Components\Select::make('empresa_id')
                                ->label('Empresa')
                                ->relationship('empresa', 'nombre')
                                ->required()
                                ->searchable(),

                            Forms\Components\DatePicker::make('fecha_realizada')
                                ->label('Fecha Realizada')
                                ->required(),
                        ]),
                ]),
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
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver'),
                    Tables\Actions\EditAction::make()
                        ->label('Editar'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenCompras::route('/'),
            'create' => Pages\CreateOrdenCompras::route('/create'),
            'edit' => Pages\EditOrdenCompras::route('/{record}/edit'),
        ];
    }
}