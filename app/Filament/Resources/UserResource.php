<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Empresa;
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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                // Campo para asignar la Empresa (solo visible para el 'root')
                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->options(Empresa::all()->pluck('nombre', 'id'))
                    ->searchable()
                    ->visible(fn () => auth()->user()->hasRole('root')),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (Page $livewire) => ($livewire instanceof CreateRecord))
                    ->maxLength(255),
                
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
                    
                Forms\Components\Select::make('Permisos')
                    ->multiple()
                    ->relationship('permissions', 'name')->preload()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
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