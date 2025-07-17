<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonaResource\Pages;
use App\Filament\Resources\PersonaResource\RelationManagers;
use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;

class PersonaResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('dni')->label('DNI')->required()->maxLength(20),
                Forms\Components\TextInput::make('primer_nombre')->label('Primer Nombre')->required()->maxLength(50),
                Forms\Components\TextInput::make('segundo_nombre')->label('Segundo Nombre')->maxLength(50)->default(null),
                Forms\Components\TextInput::make('primer_apellido')->label('Primer Apellido')->required()->maxLength(50),
                Forms\Components\TextInput::make('segundo_apellido')->label('Segundo Apellido')->maxLength(50)->default(null),
                Forms\Components\Select::make('sexo')->label('Sexo')->options([
                    'MASCULINO' => 'Masculino',
                    'FEMENINO' => 'Femenino',
                    'OTRO' => 'Otro',
                ])->required(),
                Forms\Components\DatePicker::make('fecha_nacimiento')->label('Fecha de nacimiento')->required(),
                Forms\Components\TextInput::make('telefono')->label('Teléfono')->maxLength(20),
                Forms\Components\Textarea::make('direccion')->label('Dirección')->maxLength(255),
                Forms\Components\TextInput::make('empresa_id')->label('Empresa'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dni')->label('DNI')->searchable(),
                Tables\Columns\TextColumn::make('primer_nombre')->label('Primer Nombre')->searchable(),
                Tables\Columns\TextColumn::make('primer_apellido')->label('Primer Apellido')->searchable(),
                Tables\Columns\TextColumn::make('empresa.nombre')->label('Empresa')->sortable(),
                Tables\Columns\TextColumn::make('pais.nombre_pais')->label('País')->sortable(),
                Tables\Columns\TextColumn::make('departamento.nombre_departamento')->label('Departamento')->sortable(),
                Tables\Columns\TextColumn::make('municipio.nombre_municipio')->label('Municipio')->sortable(),
                Tables\Columns\TextColumn::make('telefono')->label('Teléfono'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('empresa_id')
                    ->label('Empresa')
                    ->options(\App\Models\Empresa::pluck('nombre', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('pais_id')
                    ->label('País')
                    ->options(\App\Models\Paises::pluck('nombre_pais', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('departamento_id')
                    ->label('Departamento')
                    ->options(\App\Models\Departamento::pluck('nombre_departamento', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('municipio_id')
                    ->label('Municipio')
                    ->options(\App\Models\Municipio::pluck('nombre_municipio', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'view' => Pages\ViewPersona::route('/{record}'),
            'edit' => Pages\EditPersona::route('/{record}/edit'),
        ];
    }

    // Formulario para la vista (View) de Persona
    public static function getViewForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de Cliente')
                ->schema([
                    Forms\Components\TextInput::make('numero_cliente')->label('Número de Cliente')->disabled(),
                    Forms\Components\TextInput::make('RTN')->label('RTN')->disabled(),
                    Forms\Components\TextInput::make('empresa.nombre')->label('Empresa')->disabled(),
                ]),
            Forms\Components\Section::make('Datos de Persona')
                ->schema([
                    Forms\Components\TextInput::make('dni')->label('DNI')->disabled(),
                    Forms\Components\TextInput::make('primer_nombre')->label('Primer Nombre')->disabled(),
                    Forms\Components\TextInput::make('segundo_nombre')->label('Segundo Nombre')->disabled(),
                    Forms\Components\TextInput::make('primer_apellido')->label('Primer Apellido')->disabled(),
                    Forms\Components\TextInput::make('segundo_apellido')->label('Segundo Apellido')->disabled(),
                    Forms\Components\Textarea::make('direccion')->label('Dirección')->disabled(),
                    Forms\Components\TextInput::make('telefono')->label('Teléfono')->disabled(),
                    Forms\Components\TextInput::make('sexo')->label('Sexo')->disabled(),
                    Forms\Components\DatePicker::make('fecha_nacimiento')->label('Fecha de nacimiento')->disabled(),
                    Forms\Components\TextInput::make('pais.nombre_pais')->label('País')->disabled(),
                    Forms\Components\TextInput::make('departamento.nombre_departamento')->label('Departamento')->disabled(),
                    Forms\Components\TextInput::make('municipio.nombre_municipio')->label('Municipio')->disabled(),
                    Forms\Components\TextInput::make('empresa.nombre')->label('Empresa de la Persona')->disabled(),
                ]),
        ]);
    }
}
