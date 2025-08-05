<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioInsumosResource\Pages;
use App\Models\InventarioInsumos;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventarioInsumosResource extends Resource
{
    protected static ?string $model = InventarioInsumos::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Gestión de Inventario Insumos';
    protected static ?string $modelLabel = 'Inventario de Insumos';
    protected static ?string $navigationGroup = 'Insumos y Materia Prima';
    protected static bool $shouldRegisterNavigation = true;

    // ❌ No se permite crear registros manuales
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Insumo')
                    ->schema([
                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'nombre')
                            ->searchable()
                            ->required()
                            ->hidden()
                            ->default(fn () => Filament::auth()->user()?->empresa_id)
                            ->disabled()
                            ->dehydrated(true),

                        Forms\Components\Select::make('producto_id')
                            ->relationship('producto', 'nombre')
                            ->label('Insumo')
                            ->disabled(), // se gestiona desde orden de compra

                        Forms\Components\TextInput::make('cantidad')
                            ->numeric()
                            ->required()
                            ->label('Cantidad Disponible'),
                    ])->columns(2),

                Forms\Components\Section::make('Costo')
                    ->schema([
                        Forms\Components\TextInput::make('precio_costo')
                            ->label('Precio de Costo')
                            ->numeric()
                            ->required()
                            ->prefix('HNL'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('producto.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Insumo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('cantidad')
                    ->numeric()
                    ->sortable()
                    ->label('Cantidad'),

                Tables\Columns\TextColumn::make('precio_costo')
                    ->numeric()
                    ->sortable()
                    ->money('HNL')
                    ->label('Precio de Costo'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarioInsumos::route('/'),
            'edit' => Pages\EditInventarioInsumos::route('/{record}/edit'),
        ];
    }
}
