<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MunicipioResource\Pages;
use App\Models\Municipio;
use App\Models\Departamento;
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
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Collection;

class MunicipioResource extends Resource
{
    protected static ?string $model = Municipio::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    // --- ETIQUETAS PRINCIPALES ---
    protected static ?string $modelLabel = 'Municipio';
    protected static ?string $pluralModelLabel = 'Municipios';
    protected static ?string $navigationLabel = 'Municipios';
    // Grupo de navegación consistente
    protected static ?string $navigationGroup = 'Geografía';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo auxiliar para filtrar
                Forms\Components\Select::make('pais_id')
                    ->label('País')
                    ->options(Paises::query()->pluck('nombre_pais', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('departamento_id', null))
                    ->dehydrated(false) // No se guarda, es solo para la UI
                    ->required(),

                Forms\Components\Select::make('departamento_id')
                    ->label('Departamento')
                    ->options(function (Get $get): Collection {
                        $paisId = $get('pais_id');
                        if (!$paisId) {
                            return collect();
                        }
                        return Departamento::where('pais_id', $paisId)->pluck('nombre_departamento', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->hint('Seleccione un País para ver los Departamentos.')
                    ->hidden(fn (Get $get) => !$get('pais_id')),

                Forms\Components\TextInput::make('nombre_municipio')
                    ->label('Nombre del Municipio')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Buscar por municipio, depto. o país...')
            ->columns([
                Tables\Columns\TextColumn::make('nombre_municipio')
                    ->label('Municipio')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departamento.nombre_departamento')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departamento.pais.nombre_pais')
                    ->label('País')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('pais')
                    ->relationship('departamento.pais', 'nombre_pais')
                    ->label('Filtrar por País')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('departamento')
                    ->relationship('departamento', 'nombre_departamento')
                    ->label('Filtrar por Departamento')
                    ->preload()
                    ->searchable(),
                TrashedFilter::make()
                    ->label('Ver Eliminados'),
            ])
            // --- ACCIONES CON ORDEN Y ETIQUETAS EN ESPAÑOL ---
            ->actions([
                EditAction::make()->label('Editar'),
                ViewAction::make()->label('Ver'),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar Municipio')
                    ->modalDescription('¿Está seguro de que desea eliminar este municipio?')
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
