<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Validation\ValidationException;


class RoleResource extends Resource
{
    protected static ?string $model = SpatieRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Configuraciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->minLength(2)
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    // Añadimos una validación personalizada para la creación del rol 'root'
                    ->afterStateUpdated(function ($state, $set) {
                    // Prevenir la creación del rol 'root' por no-roots
                    if ($state === 'root' && !auth()->user()->hasRole('root')) {
                        // Lanzamos una excepción de validación
                        throw ValidationException::withMessages([
                            'name' => 'No tiene permiso para crear un rol con este nombre.',
                        ]);
                    }
                }),
                Select::make('Permisos')
                    ->multiple()
                    ->relationship('permissions', 'name')->preload()
                    
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')
                    ->label('Nombre del Rol')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d-M-Y')->sortable()
                    
                   
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('Ver'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->label('Borrar')
                    ->requiresConfirmation()
                    ->successNotificationTitle('Rol eliminado con éxito')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

        public static function getEloquentQuery(): Builder
    {
        // Obtenemos la consulta base de todos los roles.
        $query = parent::getEloquentQuery();

        // Verificamos si el usuario actual NO tiene el rol 'root'.
        if (!auth()->user()->hasRole('root')) {
            // Si no es root, añadimos una condición para excluir
            // el rol cuyo nombre sea 'root'.
            $query->where('name', '!=', 'root');
        }

        // Devolvemos la consulta (modificada o no).
        return $query;
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
