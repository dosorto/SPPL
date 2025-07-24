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
                                ->label('Tipo de persona')
                                ->options([
                                    'natural' => 'Persona Natural',
                                    'juridica' => 'Persona Jurídica',
                                ])
                                ->default('natural')
                                ->required()
                                ->reactive(),
                            Forms\Components\TextInput::make('persona.dni')
                                ->label('DNI')
                                ->required(),
                            Forms\Components\TextInput::make('persona.primer_nombre')
                                ->label('Primer Nombre')
                                ->required(),
                            Forms\Components\TextInput::make('persona.segundo_nombre')
                                ->label('Segundo Nombre'),
                            Forms\Components\TextInput::make('persona.primer_apellido')
                                ->label('Primer Apellido')
                                ->required(),
                            Forms\Components\TextInput::make('persona.segundo_apellido')
                                ->label('Segundo Apellido'),
                            Forms\Components\Select::make('persona.sexo')
                                ->label('Sexo')
                                ->options([
                                    'MASCULINO' => 'Masculino',
                                    'FEMENINO' => 'Femenino',
                                    'OTRO' => 'Otro',
                                ])
                                ->required()
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') === 'natural'),
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
                            Forms\Components\Select::make('categoria_cliente_id')
                                ->label('Categoría de Cliente')
                                ->options(\App\Models\CategoriaCliente::pluck('nombre', 'id'))
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
                Tables\Columns\TextColumn::make('numero_cliente')
                    ->label('Número de Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.tipo_persona')
                    ->label('Tipo de Persona')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'juridica' => 'Persona Jurídica',
                        default => 'Persona Natural',
                    })
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
                Tables\Columns\TextColumn::make('categoriaCliente.nombre')
                    ->label('Categoría')
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
                Tables\Filters\SelectFilter::make('categoria_cliente_id')
                    ->label('Categoría de Cliente')
                    ->options(\App\Models\CategoriaCliente::pluck('nombre', 'id')->toArray()),
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
            // Primera tarjeta (Datos básicos)
            Forms\Components\Card::make([
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\Placeholder::make('tipo_persona')
                            ->label('Tipo de persona')
                            ->content(function ($record) {
                                return match($record->persona->tipo_persona ?? 'natural') {
                                    'juridica' => 'Persona Jurídica',
                                    default => 'Persona Natural',
                                };
                            }),
                        Forms\Components\Placeholder::make('fecha_nacimiento')
                            ->label('Fecha de nacimiento')
                            ->content(fn ($record) => $record->persona->fecha_nacimiento ?? 'No especificado')
                            ->visible(fn ($record) => ($record->persona->tipo_persona ?? 'natural') === 'natural'),
                        Forms\Components\Placeholder::make('sexo')
                            ->label('Sexo')
                            ->content(fn ($record) => $record->persona->sexo ?? 'No especificado')
                            ->visible(fn ($record) => ($record->persona->tipo_persona ?? 'natural') === 'natural'),
                        Forms\Components\Placeholder::make('telefono')
                            ->label('Teléfono')
                            ->content(fn ($record) => $record->persona->telefono ?? 'No especificado'),
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
                                return $record->persona && $record->persona->pais ? $record->persona->pais->nombre_pais : 'No especificado';
                            }),
                        Forms\Components\Placeholder::make('departamento')
                            ->label('Departamento')
                            ->content(function ($record) {
                                // Método 1: Buscar el departamento a través del municipio
                                if ($record->persona && $record->persona->municipio && $record->persona->municipio->departamento) {
                                    return $record->persona->municipio->departamento->nombre_departamento;
                                }
                                
                                // Método 2: Buscar directamente por departamento_id
                                if ($record->persona && $record->persona->departamento_id) {
                                    try {
                                        $departamento = \App\Models\Departamento::findOrFail($record->persona->departamento_id);
                                        return $departamento->nombre_departamento;
                                    } catch (\Exception $e) {
                                        // Continuar con el método 3 si hay error
                                    }
                                }
                                
                                // Método 3: Buscar directamente a través de la relación departamento
                                if ($record->persona && $record->persona->departamento) {
                                    return $record->persona->departamento->nombre_departamento;
                                }
                                
                                return 'No especificado';
                            }),
                        Forms\Components\Placeholder::make('municipio')
                            ->label('Municipio')
                            ->content(function ($record) {
                                return $record->persona && $record->persona->municipio ? $record->persona->municipio->nombre_municipio : 'No especificado';
                            }),
                    ]),
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Placeholder::make('direccion')
                            ->label('Dirección')
                            ->content(fn ($record) => $record->persona->direccion ?? 'No especificado'),
                    ]),
            ])->columnSpanFull(),

            // Título DATOS DEL CLIENTE
            Forms\Components\Placeholder::make('datos_cliente_title')
                ->label('DATOS DEL CLIENTE')
                ->content('')
                ->extraAttributes(['class' => 'text-xl font-bold py-2']),
            
            // Tarjeta de Datos del Cliente
            Forms\Components\Card::make([
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\Placeholder::make('numero_cliente')
                            ->label('Número de Cliente')
                            ->content(fn ($record) => $record->numero_cliente ?? 'No especificado'),
                        Forms\Components\Placeholder::make('dni')
                            ->label('DNI')
                            ->content(fn ($record) => $record->persona->dni ?? 'No especificado'),
                        Forms\Components\Placeholder::make('rtn')
                            ->label('RTN')
                            ->content(function ($record) {

                                return isset($record->RTN) && !empty($record->RTN) 
                                    ? $record->RTN 
                                    : 'No especificado';
                            }),
                        Forms\Components\Placeholder::make('empresa')
                            ->label('Empresa')
                            ->content(fn ($record) => optional($record->empresa)->nombre ?? 'No especificado'),
                        Forms\Components\Placeholder::make('categoria_cliente')
                            ->label('Categoría de Cliente')
                            ->content(fn ($record) => optional($record->categoriaCliente)->nombre ?? 'No especificado'),
                    ]),
            ])->columnSpanFull(),
            
            // Título HISTORIAL DE COMPRAS
            Forms\Components\Placeholder::make('historial_title')
                ->label('HISTORIAL DE COMPRAS')
                ->content('')
                ->extraAttributes(['class' => 'text-xl font-bold py-2']),
                
            // Tarjeta de Historial de Compras
            Forms\Components\Card::make([
                Forms\Components\View::make('components.historial-compras')
                    ->viewData(['cliente' => fn ($record) => $record])
                    ->columnSpanFull(),
            ])->columnSpanFull(),

           
        ]);
    }

}
