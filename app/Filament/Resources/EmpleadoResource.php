<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoResource\Pages;
use App\Filament\Resources\EmpleadoResource\RelationManagers;
use App\Models\Empleado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;

class EmpleadoResource extends Resource
{
    protected static ?string $model = Empleado::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Recursos Humanos';

    // cambio jessuri: Personaliza el formulario y la tabla para empleados mostrando los campos principales y relaciones.
    // cambio jessuri: Wizard de 3 pasos para crear empleado y persona juntos
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    // cambio jessuri: Wizard con 3 pasos para crear empleado y persona juntos
                    // Paso 1: Datos personales

                    Forms\Components\Wizard\Step::make('Datos personales')
                        ->schema([
                            Forms\Components\Hidden::make('persona_autocompletada')
                                ->default(false),
                            Forms\Components\TextInput::make('persona.dni')
                                ->label('DNI')
                                ->required()
                                ->reactive()
                                ->rules(function (callable $get, $record) {
                                    // Solo aplicar la regla unique si NO está autocompletando
                                    if ($get('persona_autocompletada')) {
                                        return [];
                                    }
                                    return [
                                        Rule::unique('personas', 'dni')
                                            ->ignore($record?->persona_id)
                                    ];
                                })
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
                                    $persona = \App\Models\Persona::with(['municipio.departamento'])->where('dni', $state)->first();
                                    
                                    if ($persona) {
                                        // Marcar como autocompletada
                                        $set('persona_autocompletada', true);
                                        
                                        // Auto-completar todos los datos de la persona
                                        $set('persona.primer_nombre', $persona->primer_nombre);
                                        $set('persona.segundo_nombre', $persona->segundo_nombre);
                                        $set('persona.primer_apellido', $persona->primer_apellido);
                                        $set('persona.segundo_apellido', $persona->segundo_apellido);
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
                                        \Filament\Notifications\Notification::make()
                                            ->title('Persona encontrada')
                                            ->body('Se han completado automáticamente los datos de: ' . $persona->primer_nombre . ' ' . $persona->primer_apellido . '. Los campos de persona están bloqueados.')
                                            ->success()
                                            ->send();
                                    } else {
                                        // No se encontró persona, permitir edición
                                        $set('persona_autocompletada', false);
                                    }
                                })
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('persona.primer_nombre')
                                ->label('Primer nombre')
                                ->placeholder('Ingrese el primer nombre')
                                ->required()
                                ->maxLength(50)
                                ->minLength(2)
                                ->regex('/^[\pL\s\-]+$/u') // solo letras, espacios y guiones
                                ->extraAttributes(['autocomplete' => 'given-name'])
                                ->disabled(fn (callable $get) => $get('persona_autocompletada'))
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('persona.segundo_nombre')
                                ->label('Segundo nombre')
                                ->placeholder('Ingrese el segundo nombre (opcional)')
                                ->disabled(fn (callable $get) => $get('persona_autocompletada'))
                                ->maxLength(50)
                                ->regex('/^[\pL\s\-]+$/u')
                                ->extraAttributes(['autocomplete' => 'additional-name'])
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('persona.primer_apellido')
                                ->label('Primer apellido')
                                ->placeholder('Ingrese el primer apellido')
                                ->required()
                                ->maxLength(50)
                                ->minLength(2)
                                ->regex('/^[\pL\s\-]+$/u')
                                ->extraAttributes(['autocomplete' => 'family-name'])
                                ->disabled(fn (callable $get) => $get('persona_autocompletada'))
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('persona.segundo_apellido')
                                ->label('Segundo apellido')
                                ->placeholder('Ingrese el segundo apellido (opcional)')
                                ->maxLength(50)
                                ->regex('/^[\pL\s\-]+$/u')
                                ->extraAttributes(['autocomplete' => 'family-name'])
                                ->disabled(fn (callable $get) => $get('persona_autocompletada'))
                                ->columnSpanFull(),


                            // NUEVO: Select país
                            Forms\Components\Select::make('persona.pais_id')
                                ->label('País')
                                ->options(\App\Models\Paises::pluck('nombre_pais', 'id'))
                                ->searchable()
                                ->reactive()
                                ->required()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada'))
                                ->columnSpanFull(),

                            // NUEVO: Select departamento (filtrado por pais)
                            Forms\Components\Select::make('persona.departamento_id')
                                ->label('Departamento')
                                ->options(function (callable $get) {
                                    $paisId = $get('persona.pais_id');
                                    return $paisId
                                        ? \App\Models\Departamento::where('pais_id', $paisId)->pluck('nombre_departamento', 'id')
                                        : [];
                                })
                                ->searchable()
                                ->reactive()
                                ->required()
                                ->disabled(fn (callable $get) => !$get('persona.pais_id') || $get('persona_autocompletada'))
                                ->columnSpanFull(),

                            // NUEVO: Select municipio (filtrado por departamento)
                            Forms\Components\Select::make('persona.municipio_id')
                                ->label('Municipio')
                                ->options(function (callable $get) {
                                    $departamentoId = $get('persona.departamento_id');
                                    return $departamentoId
                                        ? \App\Models\Municipio::where('departamento_id', $departamentoId)->pluck('nombre_municipio', 'id')
                                        : [];
                                })
                                ->searchable()
                                ->reactive()
                                ->required()
                                ->disabled(fn (callable $get) => !$get('persona.departamento_id') || $get('persona_autocompletada'))
                                ->columnSpanFull(),

                            // Dirección al final
                            Forms\Components\TextInput::make('persona.direccion')
                                ->label('Dirección')
                                ->required()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada'))
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('persona.telefono')
                                ->label('Teléfono')
                                ->disabled(fn (callable $get) => $get('persona_autocompletada'))
                                ->columnSpanFull(),
                            
                            Forms\Components\Select::make('persona.sexo')
                                ->label('Sexo')
                                ->options(['MASCULINO' => 'Masculino', 'FEMENINO' => 'Femenino'])
                                ->required()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada'))
                                ->columnSpanFull(),
                            
                            Forms\Components\DatePicker::make('persona.fecha_nacimiento')
                                ->label('Fecha de nacimiento')
                                ->required()
                                ->disabled(fn (callable $get) => $get('persona_autocompletada'))
                                ->columnSpanFull(),
                        ])->columns(2)->columnSpanFull(),

                    // Paso 2: Datos de empleado
                    Forms\Components\Wizard\Step::make('Datos de empleado')
                        ->schema([
                            Forms\Components\DatePicker::make('fecha_ingreso')->label('Fecha de ingreso')->required()->columnSpanFull(),
                            Forms\Components\TextInput::make('salario')
                                ->label('Salario')
                                ->numeric()
                                ->required()
                                ->live(onBlur: true)
                                ->columnSpanFull(),
                            Forms\Components\Select::make('tipo_empleado_id')
                                ->label('Tipo de empleado')
                                ->relationship('tipoEmpleado', 'nombre_tipo')
                                ->required()->columnSpanFull(),

                            Forms\Components\Section::make('Deducciones aplicables')
                                ->schema([
                                    Forms\Components\CheckboxList::make('deducciones')
                                        ->label('Deducciones que aplican al empleado')
                                        ->options(\App\Models\Deducciones::pluck('deduccion', 'id'))
                                        ->columns(2)
                                        ->helperText('Selecciona las deducciones que aplican a este empleado.'),
                                ])
                                ->collapsible(),
                        ])->columns(2)->columnSpanFull(),
                    // Paso 3: Empresa y departamento
                    Forms\Components\Wizard\Step::make('Empresa y departamento')
                        ->schema([
                            Forms\Components\Select::make('empresa_id')
                                ->label('Empresa')
                                ->relationship('empresa', 'nombre')
                                ->required()
                                ->default(fn () => Filament::auth()->user()?->empresa_id) // asigna por defecto la empresa del usuario autenticado
                                ->disabled(fn () => true)                                 // evita que el usuario la cambie
                                ->dehydrated(true)                                        // envía el valor aunque esté deshabilitado
                                ->reactive()
                                ->columnSpanFull(),
                    Forms\Components\Select::make('departamento_empleado_id')
                        ->label('Departamento')
                        ->options(\App\Models\DepartamentoEmpleado::pluck('nombre_departamento_empleado', 'id'))
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
                            ])->columnSpan('full'),
                        ]);
                }

    public static function table(Table $table): Table
    {
        // cambio jessuri: Mejora visual de la tabla empleados con formatos, badges y búsqueda.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_empleado')
                    ->label('Número')
                    ->searchable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('persona.primer_nombre')
                    ->label('Primer nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.primer_apellido')
                    ->label('Primer apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departamento.nombre_departamento_empleado')
                    ->label('Departamento')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('tipoEmpleado.nombre_tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($record) => $record->tipoEmpleado->nombre_tipo === 'Administrativo' ? 'primary' : 'warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('empresa_id')
                    ->label('Empresa')
                    ->relationship('empresa', 'nombre'),
                Tables\Filters\SelectFilter::make('departamento_empleado_id')
                    ->label('Departamento')
                    ->relationship('departamento', 'nombre_departamento_empleado'),
                Tables\Filters\SelectFilter::make('tipo_empleado_id')
                    ->label('Tipo')
                    ->relationship('tipoEmpleado', 'nombre_tipo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record])),
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
            'index' => Pages\ListEmpleados::route('/'),
            'create' => Pages\CreateEmpleado::route('/create'),
            'edit' => Pages\EditEmpleado::route('/{record}/edit'),
            'view' => Pages\ViewEmpleado::route('/{record}'),
        ];
    }
}
