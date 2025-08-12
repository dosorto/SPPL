<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Paises;
use App\Models\Departamento;
use App\Models\Municipio;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use App\Models\Empleado;
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Components\Hidden;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Configuraciones';
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';


    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->unique(ignoreRecord: true) // <- ignora el registro en edición
                ->maxLength(255),

            TextInput::make('email')
                ->label('Correo')
                ->email()
                ->required()
                ->unique(ignoreRecord: true) // <- ignora el registro en edición
                ->maxLength(255),

            Select::make('empresa_id')
                ->label('Empresa')
                ->options(Empresa::pluck('nombre', 'id'))
                ->searchable()
                ->live()
                ->required()
                ->visible(fn () => auth()->user()->hasRole('root')),

            Hidden::make('empresa_id')
                ->default(fn () => auth()->user()->empresa_id)
                ->dehydrated()
                ->visible(fn () => ! auth()->user()->hasRole('root')),

            Select::make('persona_id')
                ->label('Persona')
                ->searchable()
                ->getSearchResultsUsing(function (string $search, Get $get) {
                    return Persona::where('empresa_id', $get('empresa_id'))
                        ->where(function ($query) use ($search) {
                            $query->where('dni', 'like', "%{$search}%")
                                ->orWhere('primer_nombre', 'like', "%{$search}%")
                                ->orWhere('primer_apellido', 'like', "%{$search}%");
                        })
                        ->limit(10)
                        ->get()
                        ->mapWithKeys(fn ($p) => [
                            $p->id => "{$p->dni} - {$p->primer_nombre} {$p->primer_apellido}",
                        ]);
                })
                ->getOptionLabelUsing(function ($value) {
                    $p = Persona::find($value);
                    return $p ? "{$p->dni} - {$p->primer_nombre} {$p->primer_apellido}" : null;
                })
                ->createOptionForm([
                    TextInput::make('dni')->label('DNI')->required(),
                    Select::make('tipo_persona')
                        ->label('Tipo de Persona')
                        ->options([
                            'natural'  => 'Natural',
                            'juridica' => 'Jurídica',
                        ])->required(),
                    TextInput::make('primer_nombre')->label('Primer Nombre')->required(),
                    TextInput::make('segundo_nombre')->label('Segundo Nombre'),
                    TextInput::make('primer_apellido')->label('Primer Apellido')->required(),
                    TextInput::make('segundo_apellido')->label('Segundo Apellido'),
                    Select::make('sexo')->label('Sexo')->options([
                        'MASCULINO' => 'Masculino',
                        'FEMENINO'  => 'Femenino',
                        'OTRO'      => 'Otro',
                    ])->required(),
                    DatePicker::make('fecha_nacimiento')->label('Fecha de nacimiento')->required(),
                    TextInput::make('telefono')->label('Teléfono'),
                    Textarea::make('direccion')->label('Dirección')->required(),
                    Hidden::make('empresa_id')->default(fn () => auth()->user()->empresa_id),

                    Select::make('pais_id')
                        ->label('País')
                        ->options(Paises::pluck('nombre_pais', 'id'))
                        ->searchable()
                        ->preload()
                        ->optionsLimit(15)
                        ->required()
                        ->reactive(),

                    Select::make('departamento_id')
                        ->label('Departamento')
                        ->searchable()
                        ->options(fn (Get $get) =>
                            Departamento::where('pais_id', $get('pais_id'))
                                ->limit(15)->pluck('nombre_departamento', 'id')
                        )
                        ->getSearchResultsUsing(fn (string $search, Get $get) =>
                            Departamento::where('pais_id', $get('pais_id'))
                                ->where('nombre_departamento', 'like', "%{$search}%")
                                ->limit(10)->pluck('nombre_departamento', 'id')
                        )
                        ->getOptionLabelUsing(fn ($value) =>
                            Departamento::find($value)?->nombre_departamento ?? 'Selecciona...'
                        )
                        ->preload()
                        ->optionsLimit(15)
                        ->required()
                        ->reactive(),

                    Select::make('municipio_id')
                        ->label('Municipio')
                        ->searchable()
                        ->options(fn (Get $get) =>
                            Municipio::where('departamento_id', $get('departamento_id'))
                                ->limit(15)->pluck('nombre_municipio', 'id')
                        )
                        ->getSearchResultsUsing(fn (string $search, Get $get) =>
                            Municipio::where('departamento_id', $get('departamento_id'))
                                ->where('nombre_municipio', 'like', "%{$search}%")
                                ->limit(10)->pluck('nombre_municipio', 'id')
                        )
                        ->getOptionLabelUsing(fn ($value) =>
                            Municipio::find($value)?->nombre_municipio ?? 'Selecciona...'
                        )
                        ->preload()
                        ->optionsLimit(15)
                        ->required(),
                ])
                ->createOptionUsing(function (array $data) {
                    $persona = Persona::where('dni', $data['dni'])->first();
                    return $persona ? $persona->id : Persona::create($data)->id;
                })
                ->createOptionAction(fn ($action) =>
                    $action->after(function ($state, callable $set) {
                        $set('persona_id', is_object($state) ? $state->getKey() : $state);
                    })
                )
                ->required()
                ->reactive()
                // Único en users.persona_id pero ignorando el registro actual
                ->rules(function (?User $record) {
                    return [
                        Rule::unique('users', 'persona_id')->ignore($record?->id),
                    ];
                })
                // Regla de negocio: no permitir cambiar la persona en edición
                ->disabled(fn (string $context) => $context === 'edit')
                // No enviar el valor al backend en edición
                ->dehydrated(fn (string $context) => $context === 'create'),

            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->revealable()
                ->confirmed()
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create')
                ->maxLength(255),

            TextInput::make('password_confirmation')
                ->label('Confirma tu contraseña')
                ->password()
                ->revealable()
                ->required(fn (string $context): bool => $context === 'create')
                ->maxLength(255)
                ->dehydrated(false),

            Select::make('Roles')
                ->label('Roles')
                ->multiple()
                ->relationship('roles', 'name', function (Builder $query) {
                    return auth()->user()->hasRole('root')
                        ? $query
                        : $query->where('name', '!=', 'root');
                })
                ->preload()
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state) {
                    $permisos = Role::whereIn('id', $state)
                        ->with('permissions')
                        ->get()
                        ->pluck('permissions')
                        ->flatten()
                        ->pluck('id')
                        ->unique()
                        ->toArray();
                    $set('Permisos', $permisos);
                }),

            \Filament\Forms\Components\Section::make('Permisos que posee el Rol')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Select::make('Permisos')
                        ->multiple()
                        ->relationship('permissions', 'name')
                        ->preload()
                        ->disabled(),
                ]),
        ]);
    }






    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('Ver'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->label('Borrar')
                    ->requiresConfirmation()
                    ->successNotificationTitle('Usuario eliminado con éxito')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    /**
     * Filtra la consulta principal del recurso.
     * El 'root' ve a todos los usuarios.
     * Los demás roles solo ven usuarios de su propia empresa.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('root')) {
            // Si es 'root', ordena todos los usuarios por 'created_at' en descendente
            return parent::getEloquentQuery()->orderBy('created_at', 'desc');
        }

        // Si no es 'root', filtra por empresa_id y luego ordena por 'created_at' en descendente
        return parent::getEloquentQuery()
                    ->where('empresa_id', $user->empresa_id)
                    ->orderBy('created_at', 'desc');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}