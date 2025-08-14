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
use Illuminate\Support\HtmlString;
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
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Set;

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
            // ====== DATOS DE USUARIO ======
            Forms\Components\Section::make('Datos de usuario')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Correo')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Select::make('empresa_id')
                        ->label('Empresa')
                        ->options(fn () => Empresa::pluck('nombre', 'id'))
                        ->searchable()
                        ->required()
                        ->visible(fn () => auth()->user()->hasRole('root'))
                        ->live(),

                    Hidden::make('empresa_id')
                        ->default(fn () => auth()->user()->empresa_id)
                        ->dehydrated()
                        ->visible(fn () => ! auth()->user()->hasRole('root')),

                    // --- Persona global vinculada por DNI ---
                    Hidden::make('persona_id')
                        ->dehydrated()
                        ->required(fn (string $context) => $context === 'create'),

                    TextInput::make('persona_dni')
                        ->label('DNI')
                        ->placeholder('0601********')
                        ->prefixIcon('heroicon-m-identification')
                        ->disabled(fn (string $context) => $context === 'edit')
                        ->dehydrated(false)
                        ->live(debounce: 600)
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $set('persona_id', null);

                            if (! $state || strlen($state) < 6) {
                                return;
                            }

                            $p = Persona::with('municipio.departamento')->where('dni', $state)->first();
                            if ($p) {
                                $set('persona_id', $p->id);
                                \Filament\Notifications\Notification::make()
                                    ->title('Persona encontrada')
                                    ->body('El DNI existe y fue vinculado para este usuario.')
                                    ->success()->send();
                            }
                        })
                        ->helperText('Escribe el DNI. Si existe verás el icono de “ojo”; si no, usa (+) para crear.')
                        ->suffixActions([
                            // 👁️ Ver datos
                            \Filament\Forms\Components\Actions\Action::make('verPersona')
                                ->icon('heroicon-o-eye')
                                ->iconButton()
                                ->color('gray')
                                ->tooltip('Ver datos')
                                ->visible(fn (Get $get) => filled($get('persona_id')))
                                ->modalHeading('Datos de la persona vinculada')
                                ->modalSubmitAction(false)
                                ->modalCancelActionLabel('Cerrar')
                                ->form(function (Get $get) {
                                    $p = Persona::with('municipio.departamento')->find($get('persona_id'));
                                    $pais = $p?->pais_id ? Paises::find($p->pais_id)?->nombre_pais : null;
                                    $dep  = $p?->departamento_id
                                        ? (Departamento::find($p->departamento_id)?->nombre_departamento)
                                        : ($p?->municipio?->departamento?->nombre_departamento ?? null);
                                    $mun  = $p?->municipio_id ? Municipio::find($p->municipio_id)?->nombre_municipio : null;
                                    $tipo = $p?->tipo_persona === 'juridica' ? 'Jurídica' : 'Natural';
                                    $fecha = $p?->fecha_nacimiento
                                        ? \Illuminate\Support\Carbon::parse($p->fecha_nacimiento)->toDateString()
                                        : null;

                                    return [
                                        TextInput::make('dni')->label('DNI')->default($p?->dni)->disabled(),
                                        TextInput::make('tipo_persona')->label('Tipo')->default($tipo)->disabled(),
                                        TextInput::make('sexo')->label('Sexo')->default($p?->sexo)->disabled(),
                                        TextInput::make('primer_nombre')->label('Primer nombre')->default($p?->primer_nombre)->disabled(),
                                        TextInput::make('segundo_nombre')->label('Segundo nombre')->default($p?->segundo_nombre)->disabled(),
                                        TextInput::make('primer_apellido')->label('Primer apellido')->default($p?->primer_apellido)->disabled(),
                                        TextInput::make('segundo_apellido')->label('Segundo apellido')->default($p?->segundo_apellido)->disabled(),
                                        TextInput::make('pais')->label('País')->default($pais)->disabled(),
                                        TextInput::make('departamento')->label('Departamento')->default($dep)->disabled(),
                                        TextInput::make('municipio')->label('Municipio')->default($mun)->disabled(),
                                        TextInput::make('fecha_nacimiento')->label('Fecha de nacimiento')->default($fecha)->disabled(),
                                        Textarea::make('direccion')->label('Dirección')->default($p?->direccion)->disabled(),
                                        TextInput::make('telefono')->label('Teléfono')->default($p?->telefono)->disabled(),
                                    ];
                                }),

                            // ➕ Crear / Vincular existente
                            \Filament\Forms\Components\Actions\Action::make('crearPersona')
                                ->icon('heroicon-o-plus')
                                ->iconButton()
                                ->color('primary')
                                ->tooltip('Crear persona')
                                ->visible(fn (Get $get) => blank($get('persona_id')))
                                ->modalHeading('Crear persona')
                                ->modalSubmitActionLabel(fn (array $data) => filled($data['existing_id'] ?? null) ? 'Vincular existente' : 'Guardar')
                                ->form(function (Get $get) {
                                    return [
                                        Hidden::make('__view_only')->default(false),
                                        Hidden::make('existing_id'),

                                        TextInput::make('dni')
                                            ->label('DNI')
                                            ->required()
                                            ->default($get('persona_dni'))
                                            ->live(debounce: 700)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (! $state) {
                                                    $set('__view_only', false);
                                                    $set('existing_id', null);
                                                    return;
                                                }

                                                $existe = Persona::with('municipio.departamento')->where('dni', $state)->first();

                                                if ($existe) {
                                                    $set('__view_only', true);
                                                    $set('existing_id', $existe->id);

                                                    // Autocompletar TODO, incluida la fecha
                                                    $set('tipo_persona', $existe->tipo_persona);
                                                    $set('sexo', $existe->sexo);
                                                    $set('primer_nombre', $existe->primer_nombre);
                                                    $set('segundo_nombre', $existe->segundo_nombre);
                                                    $set('primer_apellido', $existe->primer_apellido);
                                                    $set('segundo_apellido', $existe->segundo_apellido);
                                                    $set('pais_id', $existe->pais_id);
                                                    $set('departamento_id', $existe->departamento_id ?? optional(optional($existe->municipio)->departamento)->id);
                                                    $set('municipio_id', $existe->municipio_id);
                                                    $set('direccion', $existe->direccion);
                                                    $set('telefono', $existe->telefono);

                                                    if ($existe->fecha_nacimiento) {
                                                        $set('fecha_nacimiento', \Illuminate\Support\Carbon::parse($existe->fecha_nacimiento)->format('Y-m-d'));
                                                    }

                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Persona ya existe')
                                                        ->body('Se muestran los datos existentes. Presiona “Vincular existente”.')
                                                        ->warning()->send();
                                                } else {
                                                    $set('__view_only', false);
                                                    $set('existing_id', null);
                                                }
                                            }),

                                        Select::make('tipo_persona')->label('Tipo de Persona')
                                            ->options(['natural' => 'Natural', 'juridica' => 'Jurídica'])
                                            ->required(fn (Get $get) => ! $get('__view_only'))
                                            ->disabled(fn (Get $get) => $get('__view_only')),

                                        TextInput::make('primer_nombre')->label('Primer Nombre')
                                            ->required(fn (Get $get) => ! $get('__view_only'))
                                            ->disabled(fn (Get $get) => $get('__view_only')),
                                        TextInput::make('segundo_nombre')->label('Segundo Nombre')
                                            ->disabled(fn (Get $get) => $get('__view_only')),
                                        TextInput::make('primer_apellido')->label('Primer Apellido')
                                            ->required(fn (Get $get) => ! $get('__view_only'))
                                            ->disabled(fn (Get $get) => $get('__view_only')),
                                        TextInput::make('segundo_apellido')->label('Segundo Apellido')
                                            ->disabled(fn (Get $get) => $get('__view_only')),

                                        Select::make('sexo')->label('Sexo')->options([
                                            'MASCULINO' => 'Masculino',
                                            'FEMENINO'  => 'Femenino',
                                            'OTRO'      => 'Otro',
                                        ])
                                            ->required(fn (Get $get) => ! $get('__view_only'))
                                            ->disabled(fn (Get $get) => $get('__view_only')),

                                        DatePicker::make('fecha_nacimiento')->label('Fecha de nacimiento')
                                            ->required(fn (Get $get) => ! $get('__view_only'))
                                            ->disabled(fn (Get $get) => $get('__view_only'))
                                            ->dehydrated(fn (Get $get) => ! $get('__view_only')),

                                        TextInput::make('telefono')->label('Teléfono')
                                            ->disabled(fn (Get $get) => $get('__view_only')),
                                        Textarea::make('direccion')->label('Dirección')
                                            ->required(fn (Get $get) => ! $get('__view_only'))
                                            ->disabled(fn (Get $get) => $get('__view_only')),

                                        Select::make('pais_id')->label('País')
                                            ->options(Paises::pluck('nombre_pais', 'id'))
                                            ->searchable()->preload()->optionsLimit(15)
                                            ->required(fn (Get $get) => ! $get('__view_only'))
                                            ->reactive()
                                            ->disabled(fn (Get $get) => $get('__view_only')),

                                        Select::make('departamento_id')->label('Departamento')
                                            ->searchable()
                                            ->options(fn (Get $get) =>
                                                Departamento::where('pais_id', $get('pais_id'))->limit(15)->pluck('nombre_departamento', 'id')
                                            )
                                            ->getSearchResultsUsing(fn (string $search, Get $get) =>
                                                Departamento::where('pais_id', $get('pais_id'))
                                                    ->where('nombre_departamento', 'like', "%{$search}%")->limit(10)->pluck('nombre_departamento', 'id')
                                            )
                                            ->getOptionLabelUsing(fn ($value) =>
                                                Departamento::find($value)?->nombre_departamento ?? 'Selecciona...'
                                            )
                                            ->preload()->optionsLimit(15)
                                            ->required(fn (Get $get) => ! $get('__view_only'))
                                            ->reactive()
                                            ->disabled(fn (Get $get) => $get('__view_only')),

                                        Select::make('municipio_id')->label('Municipio')
                                            ->searchable()
                                            ->options(fn (Get $get) =>
                                                Municipio::where('departamento_id', $get('departamento_id'))->limit(15)->pluck('nombre_municipio', 'id')
                                            )
                                            ->getSearchResultsUsing(fn (string $search, Get $get) =>
                                                Municipio::where('departamento_id', $get('departamento_id'))
                                                    ->where('nombre_municipio', 'like', "%{$search}%")->limit(10)->pluck('nombre_municipio', 'id')
                                            )
                                            ->getOptionLabelUsing(fn ($value) =>
                                                Municipio::find($value)?->nombre_municipio ?? 'Selecciona...'
                                            )
                                            ->preload()->optionsLimit(15)
                                            ->required(fn (Get $get) => ! $get('__view_only'))
                                            ->disabled(fn (Get $get) => $get('__view_only')),
                                    ];
                                })
                                ->action(function (array $data, Set $set) {
                                    // Vincular si ya existe (mismo botón)
                                    if ($id = $data['existing_id'] ?? null) {
                                        if ($p = Persona::find($id)) {
                                            $set('persona_id', $p->id);
                                            $set('persona_dni', $p->dni);

                                            \Filament\Notifications\Notification::make()
                                                ->title('Persona vinculada')
                                                ->body('Se vinculó la persona existente correctamente.')
                                                ->success()->send();
                                        }
                                        return;
                                    }

                                    // Crear si no existe
                                    if (Persona::where('dni', $data['dni'])->exists()) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('El DNI ya existe')
                                            ->body('Escribe el DNI y usa “Ver” o “Vincular existente”.')
                                            ->danger()->send();
                                        return;
                                    }

                                    $persona = Persona::create($data);
                                    $set('persona_id', $persona->id);
                                    $set('persona_dni', $persona->dni);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Persona creada')
                                        ->body('Se creó y vinculó la persona correctamente.')
                                        ->success()->send();
                                }),
                        ]),
                ])
                ->columns(2),

            // ====== SEGURIDAD ======
            Forms\Components\Section::make('Seguridad')
                ->schema([
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
                ])
                ->columns(2),

            // ====== ROLES Y PERMISOS ======
            Forms\Components\Section::make('Roles y permisos')
                ->schema([
                    Select::make('Roles')
                        ->label('Roles')
                        ->multiple()
                        ->relationship('roles', 'name', function (Builder $query) {
                            // Si NO es root, jamás mostrar el rol root
                            if (! auth()->user()->hasRole('root')) {
                                $query->where('name', '!=', 'root');
                            }
                        })
                        ->preload()
                        ->searchable()
                        ->live()
                        // Si el root asigna en una empresa distinta a la suya, deshabilitar "root"
                        // (evita mezclar relationship() con options())
                        ->disableOptionWhen(function ($value, \Filament\Forms\Get $get) {
                            $role = \Spatie\Permission\Models\Role::find($value);
                            if (($role?->name ?? null) !== 'root') {
                                return false;
                            }
                            $empresaSeleccionada = $get('empresa_id');
                            $empresaDelRoot      = auth()->user()->empresa_id;

                            return $empresaSeleccionada && $empresaSeleccionada != $empresaDelRoot;
                        })
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state) {
                            $permisos = \Spatie\Permission\Models\Role::whereIn('id', (array) $state)
                                ->with('permissions')->get()
                                ->pluck('permissions')->flatten()
                                ->pluck('id')->unique()->toArray();

                            $set('Permisos', $permisos);
                        }),

                    Forms\Components\Section::make('Permisos que posee el Rol')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            Select::make('Permisos')
                                ->multiple()
                                ->relationship('permissions', 'name')
                                ->preload()
                                ->disabled(),
                        ]),
                ])
                ->columns(1),
        ]);
    }




    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
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
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}