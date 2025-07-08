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
                            Forms\Components\TextInput::make('persona.dni')
                                ->label('DNI')
                                ->required()
                                ->rules(function (callable $get, $record) {
                                    return [
                                        Rule::unique('personas', 'dni')
                                            ->ignore($record?->persona_id)
                                    ];
                                })
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('persona.primer_nombre')->label('Primer nombre')->required()->columnSpanFull(),
                            Forms\Components\TextInput::make('persona.segundo_nombre')->label('Segundo nombre')->columnSpanFull(),
                            Forms\Components\TextInput::make('persona.primer_apellido')->label('Primer apellido')->required()->columnSpanFull(),
                            Forms\Components\TextInput::make('persona.segundo_apellido')->label('Segundo apellido')->columnSpanFull(),

                            // NUEVO: Select país
                            Forms\Components\Select::make('persona.pais_id')
                                ->label('País')
                                ->options(\App\Models\Paises::pluck('nombre_pais', 'id'))
                                ->reactive()
                                ->required()
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
                                ->reactive()
                                ->required()
                                ->disabled(fn (callable $get) => !$get('persona.pais_id'))
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
                                ->reactive()
                                ->required()
                                ->disabled(fn (callable $get) => !$get('persona.departamento_id'))
                                ->columnSpanFull(),

                            // Dirección al final
                            Forms\Components\TextInput::make('persona.direccion')->label('Dirección')->required()->columnSpanFull(),

                            Forms\Components\TextInput::make('persona.telefono')->label('Teléfono')->columnSpanFull(),
                            Forms\Components\Select::make('persona.sexo')->label('Sexo')->options(['MASCULINO' => 'Masculino', 'FEMENINO' => 'Femenino'])->required()->columnSpanFull(),
                            Forms\Components\DatePicker::make('persona.fecha_nacimiento')->label('Fecha de nacimiento')->required()->columnSpanFull(),
                        ])->columns(2)->columnSpanFull(),

                    // Paso 2: Datos de empleado
                    Forms\Components\Wizard\Step::make('Datos de empleado')
                        ->schema([
                            Forms\Components\TextInput::make('numero_empleado')
                                ->label('Número de empleado')
                                ->default(fn ($record) => $record?->numero_empleado ?? 'Se asignará automáticamente')
                                ->disabled()           // Lo hace solo lectura
                                ->dehydrated(false)    // Evita que se mande desde el form
                                ->columnSpanFull(),
                            Forms\Components\DatePicker::make('fecha_ingreso')->label('Fecha de ingreso')->required()->columnSpanFull(),
                            Forms\Components\TextInput::make('salario')
                                ->label('Salario')
                                ->numeric()
                                ->required()
                                ->live(onBlur: true)
                                ->columnSpanFull(),
                            Forms\Components\Select::make('tipo_empleado_id')->label('Tipo de empleado')->relationship('tipoEmpleado', 'nombre_tipo')->required()->columnSpanFull(),
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
                                ->options(function (callable $get) {
                                    $empresaId = $get('empresa_id');
                                    return $empresaId
                                        ? \App\Models\DepartamentoEmpleado::where('empresa_id', $empresaId)->pluck('nombre_departamento_empleado', 'id')
                                        : [];
                                })
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.primer_nombre')
                    ->label('Primer nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.primer_apellido')
                    ->label('Primer apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
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
