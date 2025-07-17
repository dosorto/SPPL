<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;

class ClienteResource extends Resource
{
    public static function getRelations(): array
    {
        return [
            RelationManagers\ComprasRelationManager::class,
        ];
    }
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Datos Generales')
                        ->schema([
                            Forms\Components\Select::make('persona.tipo_persona')
                                ->label('Tipo de Persona')
                                ->options([
                                    'natural' => 'Persona Natural',
                                    'juridica' => 'Persona Jurídica',
                                ])
                                ->required()
                                ->reactive(),
                            Forms\Components\TextInput::make('persona.primer_nombre')
                                ->label('Primer Nombre')
                                ->required()
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') !== 'juridica'),
                            Forms\Components\TextInput::make('persona.segundo_nombre')
                                ->label('Segundo Nombre')
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') !== 'juridica'),
                            Forms\Components\TextInput::make('persona.primer_apellido')
                                ->label('Primer apellido')
                                ->required()
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') !== 'juridica'),
                            Forms\Components\TextInput::make('persona.segundo_apellido')
                                ->label('Segundo apellido')
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') !== 'juridica'),
                            Forms\Components\TextInput::make('persona.razon_social')
                                ->label('Razón Social')
                                ->required()
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') === 'juridica'),
                            Forms\Components\TextInput::make('persona.dni')
                                ->label('DNI / RTN')
                                ->required(),
                            Forms\Components\Select::make('persona.sexo')
                                ->label('Sexo')
                                ->options([
                                    'MASCULINO' => 'Masculino',
                                    'FEMENINO' => 'Femenino',
                                    'OTRO' => 'Otro',
                                ])
                                ->required()
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') !== 'juridica'),
                            Forms\Components\DatePicker::make('persona.fecha_nacimiento')
                                ->label('Fecha de nacimiento')
                                ->required()
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') !== 'juridica'),
                            Forms\Components\FileUpload::make('persona.fotografia')
                                ->label('Fotografía')
                                ->image()
                                ->directory('fotografias')
                                ->nullable(),
                        ]),
                    Wizard\Step::make('Dirección')
                        ->schema([
                            Forms\Components\Select::make('persona.pais_id')
                                ->label('País')
                                ->options(\App\Models\Paises::pluck('nombre_pais', 'id'))
                                ->searchable()
                                ->required()
                                ->reactive(),
                            Forms\Components\Select::make('persona.departamento_id')
                                ->label('Departamento')
                                ->options(function (callable $get) {
                                    $paisId = $get('persona.pais_id');
                                    if (!$paisId) return [];
                                    return \App\Models\Departamento::where('pais_id', $paisId)->pluck('nombre_departamento', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->disabled(fn (callable $get) => !$get('persona.pais_id')),
                            Forms\Components\Select::make('persona.municipio_id')
                                ->label('Municipio')
                                ->options(function (callable $get) {
                                    $departamentoId = $get('persona.departamento_id');
                                    if (!$departamentoId) return [];
                                    return \App\Models\Municipio::where('departamento_id', $departamentoId)->pluck('nombre_municipio', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->disabled(fn (callable $get) => !$get('persona.departamento_id')),
                            Forms\Components\Textarea::make('persona.direccion')->label('Dirección')->required(),
                            Forms\Components\TextInput::make('persona.telefono')->label('Teléfono'),
                        ]),
                    Wizard\Step::make('Datos de Cliente')
                        ->schema([
                            Forms\Components\TextInput::make('RTN')->label('RTN')->maxLength(20)->nullable(),
                            Forms\Components\Select::make('empresa_id')
                                ->label('Empresa')
                                ->options(\App\Models\Empresa::pluck('nombre', 'id'))
                                ->disabled()
                                ->visible(fn (callable $get) => !empty($get('persona.empresa_id'))),
                        ]),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_cliente')
                    ->label('Número de Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('RTN')
                    ->label('RTN')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.dni')
                    ->label('DNI Persona')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.primer_nombre')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.primer_apellido')
                    ->label('Apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('empresa_id')
                    ->label('Empresa')
                    ->options(\App\Models\Empresa::pluck('nombre', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
            'view' => Pages\ViewCliente::route('/{record}'),
        ];
    }

    // Formulario para la vista (View) de Cliente
    public static function getViewForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('numero_cliente')->label('Número de Cliente')->disabled(),
            Forms\Components\TextInput::make('RTN')->label('RTN')->disabled(),
            Forms\Components\TextInput::make('empresa.nombre')->label('Empresa')->disabled(),
            Forms\Components\TextInput::make('persona.dni')->label('DNI')->disabled(),
            Forms\Components\TextInput::make('persona.primer_nombre')->label('Primer Nombre')->disabled(),
            Forms\Components\TextInput::make('persona.segundo_nombre')->label('Segundo Nombre')->disabled(),
            Forms\Components\TextInput::make('persona.primer_apellido')->label('Primer Apellido')->disabled(),
            Forms\Components\TextInput::make('persona.segundo_apellido')->label('Segundo Apellido')->disabled(),
            Forms\Components\Textarea::make('persona.direccion')->label('Dirección')->disabled(),
            Forms\Components\TextInput::make('persona.telefono')->label('Teléfono')->disabled(),
            Forms\Components\TextInput::make('persona.sexo')->label('Sexo')->disabled(),
            Forms\Components\DatePicker::make('persona.fecha_nacimiento')->label('Fecha de nacimiento')->disabled(),
            Forms\Components\TextInput::make('persona.pais.nombre_pais')->label('País')->disabled(),
            Forms\Components\TextInput::make('persona.departamento.nombre_departamento')->label('Departamento')->disabled(),
            Forms\Components\TextInput::make('persona.municipio.nombre_municipio')->label('Municipio')->disabled(),
            Forms\Components\TextInput::make('persona.empresa.nombre')->label('Empresa de la Persona')->disabled(),
        ]);
    }

    // Eliminar el filtro manual por empresa_id, ya que el trait TenantScoped lo aplica automáticamente
}
