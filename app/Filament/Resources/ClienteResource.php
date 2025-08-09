<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;

class ClienteResource extends Resource
{
    public static function getRelations(): array
    {
       
        return [

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'persona.municipio.departamento', 
                'persona.pais',
                'empresa', 
                'categoriaCliente',
                'facturas' => function ($query) {
                    $query->with(['empleado.persona'])
                          ->orderBy('fecha_factura', 'desc')
                          ->orderBy('id', 'desc');
                }
            ])
            ->orderByDesc('id');
    }
    
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Datos Generales')
                        ->schema([
                            Forms\Components\Hidden::make('persona_autocompletada')
                                ->default(false),
                            Forms\Components\Select::make('persona.tipo_persona')
                                ->label('Tipo de persona')
                                ->options([
                                    'natural' => 'Persona Natural',
                                    'juridica' => 'Persona Jurídica',
                                ])
                                ->default('natural')
                                ->required()
                                ->reactive()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                            Forms\Components\TextInput::make('persona.dni')
                                ->label('DNI')
                                ->required()
                                ->reactive()
                                ->rules([
                                    'required',
                                    'string',
                                    'max:20',
                                    'regex:/^(?!0+$).*/',
                                ])
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (empty($state)) {
                                        // Resetear el estado de autocompletado
                                        $set('persona_autocompletada', false);
                                        return;
                                    }
                                    
                                    // Solo buscar si el DNI tiene al menos 3 caracteres para evitar búsquedas innecesarias
                                    if (strlen($state) < 3) {
                                        return;
                                    }
                                    
                                    // Buscar persona existente por DNI
                                    $persona = Persona::with(['municipio.departamento'])->where('dni', $state)->first();
                                    
                                    if ($persona) {
                                        // Marcar como autocompletada
                                        $set('persona_autocompletada', true);
                                        
                                        // Auto-completar todos los datos de la persona
                                        $set('persona.primer_nombre', $persona->primer_nombre);
                                        $set('persona.segundo_nombre', $persona->segundo_nombre);
                                        $set('persona.primer_apellido', $persona->primer_apellido);
                                        $set('persona.segundo_apellido', $persona->segundo_apellido);
                                        $set('persona.tipo_persona', $persona->tipo_persona);
                                        $set('persona.sexo', $persona->sexo);
                                        $set('persona.fecha_nacimiento', $persona->fecha_nacimiento);
                                        $set('persona.fotografia', $persona->fotografia);
                                        
                                        // Auto-completar datos de dirección
                                        $set('persona.pais_id', $persona->pais_id);
                                        // Obtener departamento_id desde la relación municipio -> departamento o directo
                                        $departamento_id = $persona->departamento_id ?? ($persona->municipio->departamento_id ?? null);
                                        $set('persona.departamento_id', $departamento_id);
                                        $set('persona.municipio_id', $persona->municipio_id);
                                        $set('persona.direccion', $persona->direccion);
                                        $set('persona.telefono', $persona->telefono);
                                        
                                        // Mostrar notificación de éxito
                                        Notification::make()
                                            ->title('Persona encontrada')
                                            ->body('Se han completado automáticamente los datos de: ' . $persona->primer_nombre . ' ' . $persona->primer_apellido . '. Los campos de persona están bloqueados.')
                                            ->success()
                                            ->send();
                                    } else {
                                        // No se encontró persona, permitir edición
                                        $set('persona_autocompletada', false);
                                    }
                                }),
                            Forms\Components\TextInput::make('persona.primer_nombre')
                                ->label('Primer Nombre')
                                ->required()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                            Forms\Components\TextInput::make('persona.segundo_nombre')
                                ->label('Segundo Nombre')
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                            Forms\Components\TextInput::make('persona.primer_apellido')
                                ->label('Primer Apellido')
                                ->required()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                            Forms\Components\TextInput::make('persona.segundo_apellido')
                                ->label('Segundo Apellido')
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                            Forms\Components\Select::make('persona.sexo')
                                ->label('Sexo')
                                ->options([
                                    'MASCULINO' => 'Masculino',
                                    'FEMENINO' => 'Femenino',
                                    'OTRO' => 'Otro',
                                ])
                                ->required()
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') === 'natural')
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                            Forms\Components\DatePicker::make('persona.fecha_nacimiento')
                                ->label('Fecha de nacimiento')
                                ->required()
                                ->visible(fn (callable $get) => $get('persona.tipo_persona') !== 'juridica')
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                            Forms\Components\FileUpload::make('persona.fotografia')
                                ->label('Fotografía')
                                ->image()
                                ->directory('fotografias')
                                ->nullable()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                        ]),
                    Wizard\Step::make('Dirección')
                        ->schema([
                            Forms\Components\Select::make('persona.pais_id')
                                ->label('País')
                                ->options(\App\Models\Paises::pluck('nombre_pais', 'id'))
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
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
                                ->disabled(fn (callable $get) => !$get('persona.pais_id') || $get('persona_autocompletada')),
                            Forms\Components\Select::make('persona.municipio_id')
                                ->label('Municipio')
                                ->options(function (callable $get) {
                                    $departamentoId = $get('persona.departamento_id');
                                    if (!$departamentoId) return [];
                                    return \App\Models\Municipio::where('departamento_id', $departamentoId)->pluck('nombre_municipio', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->disabled(fn (callable $get) => !$get('persona.departamento_id') || $get('persona_autocompletada')),
                            Forms\Components\Textarea::make('persona.direccion')
                                ->label('Dirección')
                                ->required()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                            Forms\Components\TextInput::make('persona.telefono')
                                ->label('Teléfono')
                                ->rules([
                                    'nullable',
                                    'string',
                                    'max:20',
                                    'regex:/^(?!0+$).*/',
                                ])
                                ->disabled(fn (callable $get) => $get('persona_autocompletada')),
                        ]),
                    Wizard\Step::make('Datos de Cliente')
                        ->schema([
                            Forms\Components\TextInput::make('rtn')
                                ->label('RTN')
                                ->maxLength(20)
                                ->nullable()
                                ->unique(Cliente::class, 'rtn', ignoreRecord: true)
                                ->rules([
                                    'nullable',
                                    'string',
                                    'max:20',
                                    'regex:/^(?!0+$).*/',
                                ]),
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

    // Formulario específico para edición de Cliente
    public static function getEditForm(Form $form): Form
    {
        return $form->schema([
            // Sección de Datos Personales
            Forms\Components\Section::make('Datos Personales')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('persona.dni')
                                ->label('DNI')
                                ->required()
                                ->maxLength(20)
                                ->unique(Persona::class, 'dni', ignoreRecord: true)
                                ->rules([
                                    'required',
                                    'string',
                                    'max:20',
                                    'regex:/^(?!0+$).*/',
                                ]),
                            Forms\Components\Select::make('persona.tipo_persona')
                                ->label('Tipo de Persona')
                                ->options([
                                    'natural' => 'Persona Natural',
                                    'juridica' => 'Persona Jurídica',
                                ])
                                ->required()
                                ->reactive(),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('persona.primer_nombre')
                                ->label('Primer Nombre')
                                ->required(),
                            Forms\Components\TextInput::make('persona.segundo_nombre')
                                ->label('Segundo Nombre'),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('persona.primer_apellido')
                                ->label('Primer Apellido')
                                ->required(),
                            Forms\Components\TextInput::make('persona.segundo_apellido')
                                ->label('Segundo Apellido'),
                        ]),
                    Forms\Components\Grid::make(3)
                        ->schema([
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
                ]),
            
            // Sección de Dirección
            Forms\Components\Section::make('Dirección')
                ->schema([
                    Forms\Components\Grid::make(3)
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
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Textarea::make('persona.direccion')
                                ->label('Dirección')
                                ->required(),
                            Forms\Components\TextInput::make('persona.telefono')
                                ->label('Teléfono')
                                ->unique(Persona::class, 'telefono', ignoreRecord: true)
                                ->rules([
                                    'nullable',
                                    'string',
                                    'max:20',
                                    'regex:/^(?!0+$).*/',
                                ]),
                        ]),
                ]),
            
            // Sección de Datos del Cliente
            Forms\Components\Section::make('Datos del Cliente')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('rtn')
                                ->label('RTN')
                                ->maxLength(20)
                                ->nullable()
                                ->unique(Cliente::class, 'rtn', ignoreRecord: true)
                                ->rules([
                                    'nullable',
                                    'string',
                                    'max:20',
                                    'regex:/^(?!0+$).*/',
                                ]),
                            Forms\Components\Select::make('categoria_cliente_id')
                                ->label('Categoría de Cliente')
                                ->options(\App\Models\CategoriaCliente::pluck('nombre', 'id'))
                                ->searchable()
                                ->nullable(),
                        ]),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Sección de Datos Personales
                Infolists\Components\Section::make('Datos Personales')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('persona.tipo_persona')
                                    ->label('Tipo de Persona')
                                    ->formatStateUsing(fn (string $state): string => match($state) {
                                        'juridica' => 'Persona Jurídica',
                                        default => 'Persona Natural',
                                    }),
                                Infolists\Components\TextEntry::make('persona.sexo')
                                    ->label('Sexo')
                                    ->visible(fn ($record) => ($record->persona->tipo_persona ?? 'natural') === 'natural'),
                                Infolists\Components\TextEntry::make('persona.fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->date()
                                    ->visible(fn ($record) => ($record->persona->tipo_persona ?? 'natural') === 'natural'),
                            ]),
                        Infolists\Components\TextEntry::make('persona.telefono')
                            ->label('Teléfono')
                            ->placeholder('No especificado'),
                    ]),

                // Sección de Dirección
                Infolists\Components\Section::make('Dirección')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('persona.pais.nombre_pais')
                                    ->label('País'),
                                Infolists\Components\TextEntry::make('persona.municipio.departamento.nombre_departamento')
                                    ->label('Departamento'),
                                Infolists\Components\TextEntry::make('persona.municipio.nombre_municipio')
                                    ->label('Municipio'),
                            ]),
                        Infolists\Components\TextEntry::make('persona.direccion')
                            ->label('Dirección')
                            ->columnSpanFull(),
                    ]),

                // Sección de Datos del Cliente
                Infolists\Components\Section::make('Datos del Cliente')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('numero_cliente')
                                    ->label('Número de Cliente'),
                                Infolists\Components\TextEntry::make('rtn')
                                    ->label('RTN')
                                    ->placeholder('No especificado'),
                                Infolists\Components\TextEntry::make('empresa.nombre')
                                    ->label('Empresa'),
                                Infolists\Components\TextEntry::make('categoriaCliente.nombre')
                                    ->label('Categoría de Cliente')
                                    ->placeholder('No especificada'),
                            ]),
                    ]),

                // Sección de Historial de Compras
                Infolists\Components\Section::make('Historial de Compras')
                    ->schema([
                        Infolists\Components\ViewEntry::make('facturas_table')
                            ->label('')
                            ->view('filament.infolists.facturas-table')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_cliente')
                    ->label('Número de Cliente')
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

}
