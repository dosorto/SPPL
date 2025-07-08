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
                Forms\Components\Select::make('tipo_persona')
                    ->label('Tipo de Persona')
                    ->options([
                        'natural' => 'Persona Natural',
                        'juridica' => 'Persona Jurídica',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\TextInput::make('primer_nombre')
                    ->label('Primer Nombre')
                    ->required()
                    ->visible(fn (callable $get) => $get('tipo_persona') !== 'juridica'),
                Forms\Components\TextInput::make('segundo_nombre')
                    ->label('Segundo Nombre')
                    ->visible(fn (callable $get) => $get('tipo_persona') !== 'juridica'),
                Forms\Components\TextInput::make('primer_apellido')
                    ->label('Primer Apellido')
                    ->required()
                    ->visible(fn (callable $get) => $get('tipo_persona') !== 'juridica'),
                Forms\Components\TextInput::make('segundo_apellido')
                    ->label('Segundo Apellido')
                    ->visible(fn (callable $get) => $get('tipo_persona') !== 'juridica'),
                Forms\Components\TextInput::make('razon_social')
                    ->label('Razón Social')
                    ->required()
                    ->visible(fn (callable $get) => $get('tipo_persona') === 'juridica'),
                Forms\Components\TextInput::make('dni')
                    ->label('DNI / RTN')
                    ->required(),
                Forms\Components\Textarea::make('direccion')->required(),
                Forms\Components\Select::make('municipio_id')
                    ->relationship('municipio', 'nombre')->required(),
                Forms\Components\Select::make('pais_id')
                    ->relationship('pais', 'nombre'),
                Forms\Components\TextInput::make('telefono'),
                Forms\Components\Select::make('sexo')
                    ->options([
                        'MASCULINO' => 'Masculino',
                        'FEMENINO' => 'Femenino',
                    ])
                    ->required()
                    ->visible(fn (callable $get) => $get('tipo_persona') !== 'juridica'),
                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->required()
                    ->visible(fn (callable $get) => $get('tipo_persona') !== 'juridica'),
                Forms\Components\FileUpload::make('fotografia'),
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