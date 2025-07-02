<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaisesResource\Pages;
use App\Models\Paises;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope; // Import correcto para Filament v3+

class PaisesResource extends Resource
{
    protected static ?string $model = Paises::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    // --- ETIQUETAS PRINCIPALES ---
    protected static ?string $modelLabel = 'País';
    protected static ?string $pluralModelLabel = 'Países';
    protected static ?string $navigationLabel = 'Países';
    protected static ?string $navigationGroup = 'Geografía';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_pais')
                    ->label('Nombre del País')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // AÑADIDO: Placeholder para la barra de búsqueda
            ->searchPlaceholder('Buscar por nombre de país...')
            ->columns([
                Tables\Columns\TextColumn::make('nombre_pais')
                    ->label('Nombre del País')
                    ->searchable() // Esto habilita la búsqueda para esta columna
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Fecha de Eliminación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Ver Eliminados'),
            ])
            ->actions([
                EditAction::make()->label('Editar'),
                ViewAction::make()->label('Ver'),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar País')
                    ->modalDescription('¿Está seguro de que desea eliminar este país? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar'),
                RestoreAction::make()->label('Restaurar'),
                ForceDeleteAction::make()->label('Eliminar Permanentemente'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar Seleccionados'),
                    RestoreBulkAction::make()->label('Restaurar Seleccionados'),
                    ForceDeleteBulkAction::make()->label('Eliminar Permanentemente Seleccionados'),
                ])->label('Acciones en Lote'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaises::route('/'),
            'create' => Pages\CreatePaises::route('/create'),
            'edit' => Pages\EditPaises::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
