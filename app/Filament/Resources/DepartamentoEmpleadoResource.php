<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartamentoEmpleadoResource\Pages;
use App\Models\DepartamentoEmpleado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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

    // cambio jessuri: Personaliza la tabla de departamentos internos con badges, colores y búsqueda
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // cambio jessuri: Columna para el nombre del departamento, con badge y búsqueda
                Tables\Columns\TextColumn::make('nombre_departamento_empleado')
                    ->label('Departamento')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(30),
            ])
            // cambio jessuri: Acciones de editar y eliminar
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
