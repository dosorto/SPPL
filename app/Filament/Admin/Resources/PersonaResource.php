<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PersonaResource\Pages;
use App\Filament\Admin\Resources\PersonaResource\RelationManagers;
use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonaResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 TextInput::make('primer_nombre')->required(),
    TextInput::make('segundo_nombre'),
    TextInput::make('primer_apellido')->required(),
    TextInput::make('segundo_apellido'),
    TextInput::make('dni')->required()->unique(),
    Textarea::make('direccion')->required(),
    Select::make('municipio_id')
        ->relationship('municipio', 'nombre')->required(),
    Select::make('pais_id')
        ->relationship('pais', 'nombre'),
    TextInput::make('telefono'),
    Select::make('sexo')
        ->options([
            'MASCULINO' => 'Masculino',
            'FEMENINO' => 'Femenino',
        ])->required(),
    DatePicker::make('fecha_nacimiento')->required(),
    FileUpload::make('fotografia'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListPersonas::route('/'),
            'create' => Pages\CreatePersona::route('/create'),
            'edit' => Pages\EditPersona::route('/{record}/edit'),
        ];
    }
}