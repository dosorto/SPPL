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

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Datos Generales')
                        ->schema([
                            Forms\Components\Select::make('tipo_persona')
                                ->label('Tipo de persona')
                                ->options([
                                    'natural' => 'Persona Natural',
                                    'juridica' => 'Persona Jurídica',
                                ])
                                ->default('natural')
                                ->required()
                                ->reactive(),
                            Forms\Components\TextInput::make('dni')
                                ->label('DNI')
                                ->required()
                                ->maxLength(20),
                            Forms\Components\TextInput::make('primer_nombre')
                                ->label('Primer Nombre')
                                ->required()
                                ->maxLength(50),
                            Forms\Components\TextInput::make('segundo_nombre')
                                ->label('Segundo Nombre')
                                ->maxLength(50),
                            Forms\Components\TextInput::make('primer_apellido')
                                ->label('Primer Apellido')
                                ->required()
                                ->maxLength(50),
                            Forms\Components\TextInput::make('segundo_apellido')
                                ->label('Segundo Apellido')
                                ->maxLength(50),
                            Forms\Components\Select::make('sexo')
                                ->label('Sexo')
                                ->options([
                                    'MASCULINO' => 'Masculino',
                                    'FEMENINO' => 'Femenino',
                                    'OTRO' => 'Otro',
                                ])
                                ->required()
                                ->visible(fn (callable $get) => $get('tipo_persona') === 'natural'),
                            Forms\Components\DatePicker::make('fecha_nacimiento')
                                ->label('Fecha de nacimiento')
                                ->required()
                                ->visible(fn (callable $get) => $get('tipo_persona') === 'natural'),
                            Forms\Components\FileUpload::make('fotografia')
                                ->label('Fotografía')
                                ->image()
                                ->directory('fotografias')
                                ->nullable(),
                        ]),
                    Wizard\Step::make('Dirección')
                        ->schema([
                            Forms\Components\Select::make('pais_id')
                                ->label('País')
                                ->options(\App\Models\Paises::pluck('nombre_pais', 'id'))
                                ->searchable()
                                ->required()
                                ->reactive(),
                            Forms\Components\Select::make('departamento_id')
                                ->label('Departamento')
                                ->options(function (callable $get) {
                                    $paisId = $get('pais_id');
                                    if (!$paisId) return [];
                                    return \App\Models\Departamento::where('pais_id', $paisId)->pluck('nombre_departamento', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->disabled(fn (callable $get) => !$get('pais_id')),
                            Forms\Components\Select::make('municipio_id')
                                ->label('Municipio')
                                ->options(function (callable $get) {
                                    $departamentoId = $get('departamento_id');
                                    if (!$departamentoId) return [];
                                    return \App\Models\Municipio::where('departamento_id', $departamentoId)->pluck('nombre_municipio', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->disabled(fn (callable $get) => !$get('departamento_id')),
                            Forms\Components\Textarea::make('direccion')->label('Dirección')->required(),
                            Forms\Components\TextInput::make('telefono')->label('Teléfono'),
                        ]),
                    Wizard\Step::make('Datos Adicionales')
                        ->schema([
                            Forms\Components\Select::make('empresa_id')
                                ->label('Empresa')
                                ->options(\App\Models\Empresa::pluck('nombre', 'id'))
                                ->searchable()
                                ->nullable(),
                        ]),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dni')->label('DNI')->searchable(),
                Tables\Columns\TextColumn::make('tipo_persona')
                    ->label('Tipo de Persona')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'juridica' => 'Persona Jurídica',
                        default => 'Persona Natural',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('primer_nombre')->label('Primer Nombre')->searchable(),
                Tables\Columns\TextColumn::make('primer_apellido')->label('Primer Apellido')->searchable(),
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
            // Primera tarjeta (Datos básicos)
            Forms\Components\Card::make([
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\Placeholder::make('tipo_persona')
                            ->label('Tipo de persona')
                            ->content(function ($record) {
                                return match($record->tipo_persona ?? 'natural') {
                                    'juridica' => 'Persona Jurídica',
                                    default => 'Persona Natural',
                                };
                            }),
                        Forms\Components\Placeholder::make('fecha_nacimiento')
                            ->label('Fecha de nacimiento')
                            ->content(fn ($record) => $record->fecha_nacimiento ?? 'No especificado')
                            ->visible(fn ($record) => ($record->tipo_persona ?? 'natural') === 'natural'),
                        Forms\Components\Placeholder::make('sexo')
                            ->label('Sexo')
                            ->content(fn ($record) => $record->sexo ?? 'No especificado')
                            ->visible(fn ($record) => ($record->tipo_persona ?? 'natural') === 'natural'),
                        Forms\Components\Placeholder::make('telefono')
                            ->label('Teléfono')
                            ->content(fn ($record) => $record->telefono ?? 'No especificado'),
                    ]),
            ])->columnSpanFull(),

            // Título DIRECCIÓN
            Forms\Components\Placeholder::make('direccion_title')
                ->label('DIRECCIÓN')
                ->content('')
                ->extraAttributes(['class' => 'text-xl font-bold pb-2']),
            
            // Tarjeta de Dirección
            Forms\Components\Card::make([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('pais')
                            ->label('País')
                            ->content(function ($record) {
                                return $record->pais ? $record->pais->nombre_pais : 'No especificado';
                            }),
                        Forms\Components\Placeholder::make('departamento')
                            ->label('Departamento')
                            ->content(function ($record) {
                                // Método 1: Buscar el departamento a través del municipio
                                if ($record->municipio && $record->municipio->departamento) {
                                    return $record->municipio->departamento->nombre_departamento;
                                }
                                
                                // Método 2: Buscar directamente por departamento_id
                                if ($record->departamento_id) {
                                    try {
                                        $departamento = \App\Models\Departamento::findOrFail($record->departamento_id);
                                        return $departamento->nombre_departamento;
                                    } catch (\Exception $e) {
                                        // Continuar con el método 3 si hay error
                                    }
                                }
                                
                                // Método 3: Buscar directamente a través de la relación departamento
                                if ($record->departamento) {
                                    return $record->departamento->nombre_departamento;
                                }
                                
                                return 'No especificado';
                            }),
                        Forms\Components\Placeholder::make('municipio')
                            ->label('Municipio')
                            ->content(function ($record) {
                                return $record->municipio ? $record->municipio->nombre_municipio : 'No especificado';
                            }),
                    ]),
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Placeholder::make('direccion')
                            ->label('Dirección')
                            ->content(fn ($record) => $record->direccion ?? 'No especificado'),
                    ]),
            ])->columnSpanFull(),

            // Título DATOS DEL CLIENTE
            Forms\Components\Placeholder::make('datos_cliente_title')
                ->label('DATOS DEL CLIENTE')
                ->content('')
                ->extraAttributes(['class' => 'text-xl font-bold py-2']),
            
            // Tarjeta de Datos del Cliente
            Forms\Components\Card::make([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('numero_cliente')
                            ->label('Número de Cliente')
                            ->content(fn ($record) => optional($record->cliente)->numero_cliente ?? 'No especificado'),
                        Forms\Components\Placeholder::make('rtn')
                            ->label('RTN')
                            ->content(function ($record) {
                                $rtn = optional($record->cliente)->rtn;
                                return ($rtn !== null && $rtn !== '') ? $rtn : 'No especificado';
                            }),
                        Forms\Components\Placeholder::make('empresa')
                            ->label('Empresa')
                            ->content(fn ($record) => optional($record->empresa)->nombre ?? 'No especificado'),
                    ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderByDesc('id'); 
    }

}
