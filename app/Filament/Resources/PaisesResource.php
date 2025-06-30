<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaisesResource\Pages;
use App\Models\Paises; // Tu modelo correcto
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
use Illuminate\Database\Eloquent\SoftDeletes; // Importa el trait para el query scope

class PaisesResource extends Resource
{
    // Asegúrate de que apunte a tu modelo Paises
    protected static ?string $model = Paises::class;

    // Icono para este recurso en la navegación
    protected static ?string $navigationIcon = 'heroicon-o-flag';

    // Etiquetas para la navegación y la interfaz
    protected static ?string $navigationLabel = 'Países'; 
    protected static ?string $pluralModelLabel = 'Países'; 
    protected static ?string $modelLabel = 'País'; 

   
    protected static ?string $navigationGroup = 'Geografía';
    protected static ?int $navigationSort = 1; // Orden dentro del grupo

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_pais')
                    ->label('Nombre del País') // Etiqueta legible en el formulario
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true), // Valida unicidad, ignorando el registro actual al editar
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_pais')
                    ->label('Nombre del País')
                    ->searchable() // Permite buscar por este campo
                    ->sortable(), // Permite ordenar por este campo
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto
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
                TrashedFilter::make(), // Filtro para mostrar registros eliminados (soft deletes)
            ])
            ->actions([
                EditAction::make(), // Acción de edición
                DeleteAction::make(), // Acción de eliminación (soft delete)
                Tables\Actions\RestoreAction::make(), // Acción para restaurar (si está eliminado)
                Tables\Actions\ForceDeleteAction::make(), // Acción para eliminar permanentemente (cuidado con esta)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
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
        // Asegura que el filtro TrashedFilter funcione correctamente para SoftDeletes
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletes::class,
            ]);
    }
}
