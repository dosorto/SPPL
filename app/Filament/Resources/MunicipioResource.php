<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MunicipioResource\Pages;
use App\Models\Municipio;
use App\Models\Departamento; // Necesitamos el modelo Departamento
use App\Models\Paises; // Necesitamos el modelo Paises para la lógica de filtrado anidado
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Forms\Get; // Importante para obtener el valor de otro campo
use Filament\Forms\Set; // Importante para establecer el valor de otro campo
use Illuminate\Support\Collection; // Para el tipo de retorno de las opciones

class MunicipioResource extends Resource
{
    protected static ?string $model = Municipio::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office'; // Icono para este recurso

    protected static ?string $navigationLabel = 'Municipios';
    protected static ?string $pluralModelLabel = 'Municipios';
    protected static ?string $modelLabel = 'Municipio';

    protected static ?string $navigationGroup = 'Geografía'; // Consistente con los demás
    protected static ?int $navigationSort = 3; // Orden dentro del grupo

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo auxiliar para seleccionar el País primero y filtrar Departamentos
                Forms\Components\Select::make('pais_id')
                    ->label('País')
                    ->options(Paises::all()->pluck('nombre_pais', 'id')) // Obtiene todos los países
                    ->searchable()
                    ->preload()
                    ->live() // Hace que este campo "escuche" los cambios en tiempo real
                    ->afterStateUpdated(function (Set $set) {
                        // Cuando el país cambia, resetea el departamento seleccionado
                        $set('departamento_id', null);
                    })
                    ->dehydrated(false), // Importante: No guarda este campo en la base de datos de municipios
                                        // Es solo para fines de UX y filtrado del siguiente Select.

                // Campo para seleccionar el Departamento, que se filtrará por el País seleccionado
                Forms\Components\Select::make('departamento_id')
                    ->label('Departamento')
                    ->options(function (Get $get): Collection {
                        $paisId = $get('pais_id');
                        if (!$paisId) {
                            return Collection::empty(); // Retorna una colección vacía si no hay país seleccionado
                        }
                        // Filtra los departamentos por el país seleccionado
                        return Departamento::where('pais_id', $paisId)
                            ->pluck('nombre_departamento', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->hint('Selecciona un País primero para ver los Departamentos.')
                    ->hidden(fn (Get $get) => !$get('pais_id')), // Oculta si no hay país seleccionado
                    
                Forms\Components\TextInput::make('nombre_municipio')
                    ->label('Nombre del Municipio')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true), // Valida unicidad, ignorando el registro actual al editar
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_municipio')
                    ->label('Nombre del Municipio')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departamento.nombre_departamento') // Muestra el nombre del departamento
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departamento.pais.nombre_pais') // Muestra el nombre del país a través del departamento
                    ->label('País')
                    ->searchable()
                    ->sortable(),
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
                // Filtro por País (filtrará municipios que pertenecen a departamentos de ese país)
                SelectFilter::make('pais')
                    ->relationship('departamento.pais', 'nombre_pais')
                    ->label('Filtrar por País')
                    ->preload()
                    ->searchable(),
                
                // Filtro por Departamento
                SelectFilter::make('departamento_id')
                    ->relationship('departamento', 'nombre_departamento')
                    ->label('Filtrar por Departamento')
                    ->preload()
                    ->searchable(),

                TrashedFilter::make(), // Filtro para soft deletes
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMunicipios::route('/'),
            'create' => Pages\CreateMunicipio::route('/create'),
            'edit' => Pages\EditMunicipio::route('/{record}/edit'),
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
