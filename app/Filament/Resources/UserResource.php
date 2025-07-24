<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Empresa;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Configuraciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('correo')
                    ->email()
                    ->required()
                    ->maxLength(255),
                

                // Campo para asignar la Empresa (solo visible para el 'root')
                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->options(Empresa::all()->pluck('nombre', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn () => auth()->user()->hasRole('root')),

                Forms\Components\Select::make('empleado_id')
                    ->label('Empleado Asociado')
                    ->options(function (Get $get) {
                        $query = Empleado::query()->whereHas('persona');

                        if (auth()->user()->hasRole('root')) {
                            $empresaId = $get('empresa_id');
                            if ($empresaId) {
                                $query->where('empresa_id', $empresaId);
                            } else {
                                return [];
                            }
                        } else {
                            $query->where('empresa_id', auth()->user()->empresa_id);
                        }

                        // --- CORRECCIÓN FINAL: Procesar la colección para evitar etiquetas nulas ---
                        return $query->with('persona')->get()
                            ->filter(function ($empleado) {
                                // Asegurarse de que la persona y su nombre completo no sean nulos o vacíos.
                                return $empleado->persona && !empty($empleado->persona->nombre_completo);
                            })
                            ->mapWithKeys(function ($empleado) {
                                // Crear el array de opciones solo con datos válidos.
                                return [$empleado->id => $empleado->persona->nombre_completo];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->placeholder('Seleccione un empleado'),

                Forms\Components\TextInput::make('password') // 1. Nombre del campo: 'password'
                    ->label('Contraseña') // Esta es la etiqueta que ve el usuario
                    ->password()
                    ->revealable() // Extra: Añade un botón para mostrar/ocultar la contraseña
                    ->confirmed()
                    
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    
                    // 3. Requerido solo al crear, no al editar (versión más limpia)
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),

                // CAMPO DE CONFIRMACIÓN DE CONTRASEÑA
                Forms\Components\TextInput::make('password_confirmation') // 1. Nombre del campo: 'password_confirmation'
                    ->label('Confirma tu contraseña') // Etiqueta para el usuario
                    ->password()
                    ->revealable()
                    
                    // 3. Requerido solo al crear
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255)
                    
                    // 4. Correcto: No se guarda en la base de datos
                    ->dehydrated(false),
                
                // --- CAMPO DE ROLES CON EL ARREGLO DE SEGURIDAD ---
                Forms\Components\Select::make('Roles')
                    ->label('Roles')
                    ->multiple()
                    ->relationship(
                        'roles',
                        'name',
                        // Se modifica la consulta para cargar los roles
                        function (Builder $query) {
                            // Si el usuario actual NO es 'root', se excluye el rol 'root' de la lista.
                            if (!auth()->user()->hasRole('root')) {
                                $query->where('name', '!=', 'root');
                            }
                        }
                    )
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $permissions = Role::whereIn('id', $state)
                        ->with('permissions')
                        ->get()
                        ->pluck('permissions')
                        ->flatten()
                        ->pluck('id')
                        ->unique()
                        ->toArray();
                        $set('Permisos', $permissions);
                    }),
                    
               Forms\Components\Section::make('Permisos que posee el Rol')
                ->collapsible() // Esto hace que la sección se pueda abrir y cerrar
                ->collapsed()   // Esto hace que empiece cerrada por defecto
                ->schema([
                    // 2. Metemos el campo de Permisos DENTRO de la sección
                    Forms\Components\Select::make('Permisos')
                        ->multiple()
                        ->relationship('permissions', 'name')->preload()
                        ->disabled(), // Lo deshabilitamos para que sea solo informativo
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('correo')
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
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('empresa_id', $user->empresa_id);
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