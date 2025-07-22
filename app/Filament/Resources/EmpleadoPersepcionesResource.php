<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoPersepcionesResource\Pages;
use App\Filament\Resources\EmpleadoPersepcionesResource\RelationManagers;
use App\Models\EmpleadoPercepciones;
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

class EmpleadoPersepcionesResource extends Resource
{
    protected static ?string $model = EmpleadoPercepciones::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('empleado_id')
                    ->label('Empleado')
                    ->options(
                        \App\Models\Empleado::all()->pluck('nombre_completo', 'id')
                    )
                    ->searchable()
                    ->required(),

                \Filament\Forms\Components\Select::make('percepcion_id')
                    ->label('Percepción')
                    ->relationship('percepcion', 'percepcion')
                    ->required()
                    ->reactive()
                    ->rule(function ($get, $context) {
                        return function ($attribute, $value, $fail) use ($get, $context) {
                            $empleadoId = $get('empleado_id');
                            $percepcionId = $value;
                            $fecha = $get('fecha_aplicacion') ?? now();
                            $mes = \Carbon\Carbon::parse($fecha)->month;
                            $anio = \Carbon\Carbon::parse($fecha)->year;
                            $query = \App\Models\EmpleadoPercepciones::query()
                                ->where('empleado_id', $empleadoId)
                                ->where('percepcion_id', $percepcionId)
                                ->whereMonth('fecha_aplicacion', $mes)
                                ->whereYear('fecha_aplicacion', $anio);
                            if ($context === 'edit') {
                                $recordId = $get('id');
                                if ($recordId) {
                                    $query->where('id', '!=', $recordId);
                                }
                            }
                            if ($query->exists()) {
                                $fail('Este empleado ya tiene esta percepción asignada para este mes.');
                            }
                        };
                    }),

                \Filament\Forms\Components\TextInput::make('cantidad_horas')
                    ->label('Cantidad de horas extras')
                    ->numeric()
                    ->minValue(1)
                    ->visible(fn ($get) => optional(\App\Models\Percepciones::find($get('percepcion_id')))->percepcion === 'Horas Extras')
                    ->dehydrated(),

                // DatePicker oculto: la fecha se asignará automáticamente en el modelo
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('empleado.nombre_completo')
                    ->label('Empleado')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('percepcion.percepcion')
                    ->label('Percepción')
                    ->sortable(),

                // No mostrar fecha_aplicacion
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
            'index' => Pages\ListEmpleadoPersepciones::route('/'),
            'create' => Pages\CreateEmpleadoPersepciones::route('/create'),
            'edit' => Pages\EditEmpleadoPersepciones::route('/{record}/edit'),
        ];
    }
}
