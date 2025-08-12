<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenProduccionResource\Pages;
use App\Filament\Resources\OrdenProduccionResource\RelationManagers;
use App\Models\OrdenProduccion;
use App\Models\Productos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdenProduccionResource extends Resource
{
    protected static ?string $model = OrdenProduccion::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Órdenes de Producción';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Órdenes de Producción';
    protected static ?string $pluralLabel = 'Órdenes de Producción';
    protected static ?string $label = 'Orden de Producción';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('empresa_id')
                    ->default(fn () => auth()->user()->empresa_id)
                    ->required(),
                Forms\Components\Select::make('producto_id')
                    ->relationship('producto', 'nombre')
                    ->required(),
                Forms\Components\TextInput::make('cantidad')
                    ->numeric()
                    ->required(),
                Forms\Components\DatePicker::make('fecha_solicitud')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_entrega'),
                Forms\Components\Select::make('estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'En Proceso' => 'En Proceso',
                        'Finalizada' => 'Finalizada',
                        'Cancelada' => 'Cancelada',
                    ])
                    ->default('Pendiente')
                    ->required(),
                Forms\Components\Textarea::make('observaciones'),
                Forms\Components\Repeater::make('insumos')
                    ->label('Insumos a utilizar')
                    ->relationship('insumos')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('insumo_id')
                                    ->label('Insumo')
                                    ->relationship('insumo', 'nombre')
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('cantidad_utilizada')
                                    ->label('Cantidad utilizada')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->minItems(1)
                    ->required()
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('producto.nombre')->label('Producto')->searchable(),
                Tables\Columns\TextColumn::make('cantidad')->sortable(),
                Tables\Columns\TextColumn::make('fecha_solicitud')->date()->sortable(),
                Tables\Columns\TextColumn::make('fecha_entrega')->date()->sortable(),
                Tables\Columns\TextColumn::make('estado')->sortable(),
                Tables\Columns\TextColumn::make('empresa.nombre')->label('Empresa')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Creado'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'En Proceso' => 'En Proceso',
                        'Finalizada' => 'Finalizada',
                        'Cancelada' => 'Cancelada',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Puedes agregar RelationManagers para insumos si lo deseas
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenProduccions::route('/'),
            'create' => Pages\CreateOrdenProduccion::route('/create'),
            'edit' => Pages\EditOrdenProduccion::route('/{record}/edit'),
        ];
    }
}
