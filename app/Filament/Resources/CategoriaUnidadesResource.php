<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaUnidadesResource\Pages;
use App\Models\CategoriaUnidades;
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

class CategoriaUnidadesResource extends Resource
{
    // Modelo asociado al recurso
    protected static ?string $model = CategoriaUnidades::class;

    // Icono y etiquetas para la navegación
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Categorías de Unidades';
    protected static ?string $pluralModelLabel = 'Categorías de Unidades';
    protected static ?string $modelLabel = 'Categoría de Unidad';
    protected static ?string $navigationGroup = 'Unidades y Medidas';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre de la Categoría')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre de la Categoría')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('creadoPor.name')
                    ->label('Creado por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('actualizadoPor.name')
                    ->label('Actualizado por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('eliminadoPor.name')
                    ->label('Eliminado por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(), // Habilita filtro para ver registros eliminados (soft delete)
            ])
            ->actions([
                EditAction::make() ->label('Editar'),
                ViewAction::make() ->label('ver'),
                DeleteAction::make() ->label('Eliminar'),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
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
        return [
            // Puedes agregar RelationManagers aquí si hay relaciones hijas
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaUnidades::route('/'),
            'create' => Pages\CreateCategoriaUnidades::route('/create'),
            'edit' => Pages\EditCategoriaUnidades::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Quita el global scope para soft deletes y habilita TrashedFilter
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletes::class,
            ]);
    }
}
