<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnidadDeMedidasResource\Pages;
use App\Models\UnidadDeMedidas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadDeMedidasResource extends Resource
{
    protected static ?string $model = UnidadDeMedidas::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Unidades de Medida';
    protected static ?string $pluralModelLabel = 'Unidades de Medida';
    protected static ?string $modelLabel = 'Unidad de Medida';
    protected static ?string $navigationGroup = 'Unidades y Medidas';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre de la Unidad')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('abreviacion')
                    ->label('Abreviación')
                    ->required()
                    ->maxLength(10),

                Forms\Components\Select::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre')
                    ->searchable()
                    ->required(),

                // Quitamos los campos created_by, updated_by, deleted_by del formulario para que no se editen manualmente
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('abreviacion')
                    ->label('Abreviación')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('creadoPor.name')
                    ->label('Creado por')
                    ->sortable(),

                Tables\Columns\TextColumn::make('actualizadoPor.name')
                    ->label('Actualizado por')
                    ->sortable(),

                Tables\Columns\TextColumn::make('eliminadoPor.name')
                    ->label('Eliminado por')
                    ->sortable()
                    ->hidden(),

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
                TrashedFilter::make(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnidadDeMedidas::route('/'),
            'create' => Pages\CreateUnidadDeMedidas::route('/create'),
            'edit' => Pages\EditUnidadDeMedidas::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletes::class,
            ]);
    }
}
