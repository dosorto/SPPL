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

class EmpleadoResource extends Resource
{
    protected static ?string $model = Empleado::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // cambio jessuri: Personaliza el formulario y la tabla para empleados mostrando los campos principales y relaciones.
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero_empleado')
                    ->label('Número de empleado')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_ingreso')
                    ->label('Fecha de ingreso')
                    ->required(),
                Forms\Components\TextInput::make('salario')
                    ->label('Salario')
                    ->numeric()
                    ->required(),
                // cambio jessuri: Usamos getOptionLabelFromRecordUsing para que 
                // Filament muestre el nombre completo generado por el accesor en Persona
                Forms\Components\Select::make('persona_id')
                    ->label('Persona')
                    ->relationship('persona', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre)
                    ->required(),
                // cambio jessuri: Ajuste para que el select de departamento muestre correctamente los departamentos internos de empleados.
                // Se usa la relación 'departamento' (que apunta a DepartamentoEmpleado) y el campo 'id', mostrando el nombre_departamento_empleado.
                Forms\Components\Select::make('departamento_empleado_id')
                    ->label('Departamento')
                    ->relationship('departamento', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre_departamento_empleado)
                    //->searchable()
                    ->required(),
                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->relationship('empresa', 'nombre')
                    ->required(),
                // cambio jessuri: Usamos getOptionLabelFromRecordUsing para mostrar 
                // el nombre correcto del tipo de empleado en el select.
                Forms\Components\Select::make('tipo_empleado_id')
                    ->label('Tipo de empleado')
                    ->relationship('tipoEmpleado', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre_tipo)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        // cambio jessuri: Muestra correctamente el nombre del departamento y tipo de empleado en la tabla usando las relaciones.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_empleado')
                    ->label('Número'),
                Tables\Columns\TextColumn::make('fecha_ingreso')
                    ->label('Ingreso'),
                Tables\Columns\TextColumn::make('salario')
                    ->label('Salario'),
                Tables\Columns\TextColumn::make('persona.nombre')
                    ->label('Persona'),
                Tables\Columns\TextColumn::make('departamento.nombre_departamento_empleado')
                    ->label('Departamento'),
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa'),
                Tables\Columns\TextColumn::make('tipoEmpleado.nombre_tipo')
                    ->label('Tipo'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        ];
    }
}
