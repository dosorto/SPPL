<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartamentoResource\Pages;
use App\Filament\Resources\DepartamentoResource\RelationManagers;
use App\Models\Departamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartamentoResource extends Resource
{
    protected static ?string $model = Departamento::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // cambio jessuri: Personaliza el formulario y la tabla para departamentos mostrando los campos principales y relaciones.
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_departamento')
                    ->label('Nombre del departamento')
                    ->required(),
                Forms\Components\Select::make('pais_id')
                    ->label('País')
                    ->relationship('pais', 'nombre_pais')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        // cambio jessuri: Configura las columnas principales y relaciones para departamentos.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_departamento')
                    ->label('Departamento'),
                Tables\Columns\TextColumn::make('pais.nombre_pais')
                    ->label('País'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDepartamentos::route('/'),
            'create' => Pages\CreateDepartamento::route('/create'),
            'edit' => Pages\EditDepartamento::route('/{record}/edit'),
        ];
    }
}
