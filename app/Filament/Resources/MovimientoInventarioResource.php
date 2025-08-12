<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoInventarioResource\Pages;
use App\Models\MovimientoInventario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MovimientoInventarioResource extends Resource
{
    protected static ?string $model = MovimientoInventario::class;
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('movimientos_inventario_ver') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('movimientos_inventario_ver') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('movimientos_inventario_eliminar') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('movimientos_inventario_crear') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('movimientos_inventario_actualizar') ?? false;
    }
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $navigationLabel = 'Movimientos de Inventario';
    protected static ?string $pluralLabel = 'Movimientos de Inventario';
    protected static ?string $label = 'Movimiento de Inventario';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('empresa_id', auth()->user()->empresa_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('producto_id')
                ->relationship('producto', 'nombre')
                ->label('Producto')
                ->required(),
            Forms\Components\Select::make('tipo')
                ->options([
                    'entrada' => 'Entrada',
                    'salida' => 'Salida',
                ])
                ->required(),
            Forms\Components\TextInput::make('cantidad')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('motivo'),
            Forms\Components\TextInput::make('referencia'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('producto.nombre')->label('Producto')->searchable(),
            Tables\Columns\TextColumn::make('tipo')->label('Tipo')->sortable(),
            Tables\Columns\TextColumn::make('cantidad')->label('Cantidad'),
            Tables\Columns\TextColumn::make('motivo')->label('Motivo'),
            Tables\Columns\TextColumn::make('referencia')->label('Referencia'),
            Tables\Columns\TextColumn::make('usuario_id')->label('Usuario'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Fecha'),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('tipo')
                ->options([
                    'entrada' => 'Entrada',
                    'salida' => 'Salida',
                ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListMovimientoInventarios::route('/'),
            'view' => Pages\ViewMovimientoInventario::route('/{record}'),
        ];
    }
}
