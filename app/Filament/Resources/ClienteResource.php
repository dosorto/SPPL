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
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Datos de la Persona')
                        ->schema([
                            Forms\Components\TextInput::make('persona.primer_nombre')->label('Primer nombre')->required(),
                            Forms\Components\TextInput::make('persona.segundo_nombre')->label('Segundo nombre'),
                            Forms\Components\TextInput::make('persona.primer_apellido')->label('Primer apellido')->required(),
                            Forms\Components\TextInput::make('persona.segundo_apellido')->label('Segundo apellido'),
                            Forms\Components\TextInput::make('persona.dni')->label('DNI')->required(),
                            Forms\Components\Textarea::make('persona.direccion')->label('Dirección')->required(),
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
                            Forms\Components\TextInput::make('persona.telefono')->label('Teléfono'),
                            Forms\Components\Select::make('persona.sexo')->label('Sexo')->options([
                                'MASCULINO' => 'Masculino',
                                'FEMENINO' => 'Femenino',
                                'OTRO' => 'Otro',
                            ])->required(),
                            Forms\Components\DatePicker::make('persona.fecha_nacimiento')->label('Fecha de nacimiento')->required(),
                            Forms\Components\FileUpload::make('persona.fotografia')->label('Fotografía')->image()->directory('fotografias')->nullable(),
                            Forms\Components\Select::make('persona.empresa_id')->label('Empresa')
                                ->options(\App\Models\Empresa::pluck('nombre', 'id'))
                                ->searchable()->nullable(),
                        ]),
                    Wizard\Step::make('Datos de Cliente')
                        ->schema([
                            Forms\Components\TextInput::make('RTN')->label('RTN')->maxLength(20)->nullable(),
                            Forms\Components\Select::make('empresa_id')
                                ->label('Empresa')
                                ->options([])
                                ->disabled()
                                ->hidden()
                                ->helperText('La empresa será la misma que la de la persona.'),
                        ]),
                ])
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
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('rtn')
                    ->form([
                        Forms\Components\TextInput::make('rtn')->label('RTN'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['rtn']) {
                            $query->where('rtn', 'like', '%'.$data['rtn'].'%');
                        }
                    }),
                Tables\Filters\Filter::make('numero_cliente')
                    ->form([
                        Forms\Components\TextInput::make('numero_cliente')->label('Número de Cliente'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['numero_cliente']) {
                            $query->where('numero_cliente', 'like', '%'.$data['numero_cliente'].'%');
                        }
                    }),
                Tables\Filters\SelectFilter::make('empresa_id')
                    ->label('Empresa')
                    ->options(\App\Models\Empresa::pluck('nombre', 'id')->toArray()),
                Tables\Filters\Filter::make('persona_dni')
                    ->form([
                        Forms\Components\TextInput::make('persona_dni')->label('DNI Persona'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['persona_dni']) {
                            $query->whereHas('persona', function ($q) use ($data) {
                                $q->where('dni', 'like', '%'.$data['persona_dni'].'%');
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()->modal(true),
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
}
