<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoDeduccionesResource\Pages;
use App\Filament\Resources\EmpleadoDeduccionesResource\RelationManagers;
use App\Models\EmpleadoDeducciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Facades\Filament;

class EmpleadoDeduccionesResource extends Resource
{
    protected static ?string $model = EmpleadoDeducciones::class;
    protected static ?string $navigationGroup = 'Recursos Humanos';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('empleado_id')
                    ->label('Empleado')
                    ->options(
                        \App\Models\Empleado::all()->pluck('nombre_completo', 'id')
                    )
                    ->searchable()
                    ->required()
                    ->reactive(),

                // Mostrar todas las deducciones disponibles
                Select::make('deduccion_id')
                    ->label('Deducción')
                    ->options(function () {
                        return \App\Models\Deducciones::all()->pluck('deduccion', 'id')->toArray();
                    })
                    ->required()
                    ->rule(function ($get, $context) {
                        return function ($attribute, $value, $fail) use ($get, $context) {
                            $empleadoId = $get('empleado_id');
                            $deduccionId = $value;
                            $fecha = $get('fecha_aplicacion') ?? now();
                            $mes = \Carbon\Carbon::parse($fecha)->month;
                            $anio = \Carbon\Carbon::parse($fecha)->year;
                            $query = \App\Models\EmpleadoDeducciones::query()
                                ->where('empleado_id', $empleadoId)
                                ->where('deduccion_id', $deduccionId)
                                ->whereMonth('fecha_aplicacion', $mes)
                                ->whereYear('fecha_aplicacion', $anio);
                            if ($context === 'edit') {
                                $recordId = $get('id');
                                if ($recordId) {
                                    $query->where('id', '!=', $recordId);
                                }
                            }
                            if ($query->exists()) {
                                $fail('Este empleado ya tiene esta deducción asignada para este mes.');
                            }
                        };
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('empleado.nombre_completo')
                    ->label('Empleado')
                    ->sortable(),

                TextColumn::make('deduccion.deduccion')
                    ->label('Deducción')
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),   
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListEmpleadoDeducciones::route('/'),
            'create' => Pages\CreateEmpleadoDeducciones::route('/create'),
            'edit' => Pages\EditEmpleadoDeducciones::route('/{record}/edit'),
        ];
    }
}
