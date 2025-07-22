<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartamentoEmpleadoResource\Pages;
use App\Models\DepartamentoEmpleado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\EmpleadoResource;
use App\Filament\Resources\EmpresaResource;
use Filament\Resources\Resource;
use Filament\Facades\Filament;

class DepartamentoEmpleadoResource extends Resource
{

    protected static ?string $model = DepartamentoEmpleado::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Departamento Empleados';
    protected static ?string $navigationGroup = 'Recursos Humanos';

    public static function form(Form $form): Form
    {
        return $form
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
                // cambio jessuri: Campo para el nombre del departamento, único y requerido
                Forms\Components\TextInput::make('nombre_departamento_empleado')
                    ->label('Nombre del departamento')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'El nombre del departamento ya está registrado.',
                        'required' => 'El nombre del departamento es obligatorio.',
                    ]),
                // cambio jessuri: Campo para la descripción del departamento
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(2),
  
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_departamento_empleado')
                    ->label('Departamento')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(30),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(), 
            ])
            //  Acciones masivas de eliminación
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
            'index' => Pages\ListDepartamentoEmpleados::route('/'),
            'create' => Pages\CreateDepartamentoEmpleado::route('/create'),
            'edit' => Pages\EditDepartamentoEmpleado::route('/{record}/edit'),
        ];
    }
}
