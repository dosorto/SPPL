<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartamentoResource\Pages;
use App\Models\Departamento;
use App\Models\Paises; // Necesitamos el modelo Paises para la relación
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter; // Para filtrar por relación
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\EditAction as TablesEditAction; // Importa Edit
// Importa el trait para el query scope

class DepartamentoResource extends Resource
{
    protected static ?string $model = Departamento::class;

    protected static ?string $navigationIcon = 'heroicon-o-map'; // Nuevo icono para Departamento

    protected static ?string $navigationLabel = 'Departamentos';
    protected static ?string $pluralModelLabel = 'Departamentos';
    protected static ?string $modelLabel = 'Departamento';

    protected static ?string $navigationGroup = 'Geografía'; // Consistente con Países
    protected static ?int $navigationSort = 2; // Orden dentro del grupo

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo para seleccionar el País (relación)
                Forms\Components\Select::make('pais_id')
                    ->label('País')
                    ->relationship('pais', 'nombre_pais') // 'pais' es el nombre del método de relación en el modelo Departamento
                    ->required()
                    ->searchable() // Permite buscar en la lista de países
                    ->preload(), // Carga todos los países para una mejor experiencia

                Forms\Components\TextInput::make('nombre_departamento')
                    ->label('Nombre del Departamento')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true), // Valida unicidad, ignorando el registro actual al editar
                
                // Los campos created_by, updated_by, deleted_by no suelen ir en el formulario para ser editados manualmente.
                // Se manejan a través de Observers o hooks si necesitas loguear el usuario.
                // Si realmente los necesitas para un propósito especial, podrías hacerlos ->disabled() y ->hidden()
                // o eliminarlos del schema del formulario. Los he quitado para una experiencia de usuario estándar.
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_departamento')
                    ->label('Nombre del Departamento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pais.nombre_pais') // Muestra el nombre del país relacionado
                    ->label('País')
                    ->searchable() // Puedes buscar por nombre de país
                    ->sortable(), // Puedes ordenar por nombre de país
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
                // Los campos _by se pueden mostrar si son relevantes para el log, pero no son obligatorios.
                Tables\Columns\TextColumn::make('created_by')
                    ->label('Creado por ID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_by')
                    ->label('Actualizado por ID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_by')
                    ->label('Eliminado por ID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('pais')
                    ->relationship('pais', 'nombre_pais')
                    ->label('Filtrar por País')
                    ->preload()
                    ->searchable(),
                TrashedFilter::make(), // Filtro para soft deletes
            ])
            ->actions([
                EditAction::make()->label('Editar'),
                ViewAction::make()->label('Ver'),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar Departamento')
                    ->modalDescription('¿Está seguro de que desea eliminar este departamento?')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar'),
                ForceDeleteAction::make()->label('Eliminar Permanentemente'),
                RestoreAction::make()->label('Restaurar'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar Seleccionados'),
                    RestoreBulkAction::make()->label('Restaurar Seleccionados'),
                    ForceDeleteBulkAction::make()->label('Eliminar Permanentemente'),
                ])->label('Acciones en Lote'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Podemos añadir un RelationManager para Municipios aquí para verlos desde el departamento
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartamentos::route('/'),
            'create' => Pages\CreateDepartamento::route('/create'),
            'edit' => Pages\EditDepartamento::route('/{record}/edit'),
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
