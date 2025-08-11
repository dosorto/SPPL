<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoOrdenComprasResource\Pages;
use App\Models\TipoOrdenCompras;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TipoOrdenComprasResource extends Resource
{
    protected static ?string $model = TipoOrdenCompras::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Tipos de Orden de Compra';
    protected static ?string $pluralModelLabel = 'Tipos de Orden de Compra';
    protected static ?string $modelLabel = 'Tipo de Orden de Compra';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del Tipo de Orden')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Ej. Orden de Compra Estándar, Urgente...')
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Tipo de Orden'),

                Tables\Columns\TextColumn::make('empresa_id')
                    ->label('ID de Empresa')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Ver eliminados'),
            ])
            ->actions([
                EditAction::make()->label('Editar'),
                ViewAction::make()->label('Ver'),
                DeleteAction::make()->label('Eliminar'),
                Tables\Actions\RestoreAction::make()->label('Restaurar'),
                Tables\Actions\ForceDeleteAction::make()->label('Eliminar Definitivamente'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar'),
                    Tables\Actions\RestoreBulkAction::make()->label('Restaurar'),
                    Tables\Actions\ForceDeleteBulkAction::make()->label('Eliminar Definitivamente'),
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
            'index' => Pages\ListTipoOrdenCompras::route('/'),
            'create' => Pages\CreateTipoOrdenCompras::route('/create'),
            'edit' => Pages\EditTipoOrdenCompras::route('/{record}/edit'),
        ];
    }

    /**
     * Aplica el scoping para que solo se muestren los registros de la empresa del usuario.
     * La lógica está en el modelo, este método solo asegura que se use.
     * Además, se elimina el `withoutGlobalScopes` para que el `TrashedFilter` funcione correctamente.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
