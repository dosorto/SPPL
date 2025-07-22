<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NominaResource\Pages;
use App\Models\Nominas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use App\Models\Empleado;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;


class NominaResource extends Resource
{
    protected static ?string $model = Nominas::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $modelLabel = 'Nómina';
    
    protected static ?string $pluralModelLabel = 'Nóminas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('empresa_id')
                ->label('Empresa')
                ->relationship('empresa', 'nombre')
                ->required()
                ->default(fn () => Filament::auth()->user()?->empresa_id)
                ->disabled()
                ->dehydrated(fn ($state) => filled($state))
                ->columnSpanFull(),

                Select::make('mes')
                    ->label('Mes')
                    ->options([
                        1 => 'Enero',
                        2 => 'Febrero',
                        3 => 'Marzo',
                        4 => 'Abril',
                        5 => 'Mayo',
                        6 => 'Junio',
                        7 => 'Julio',
                        8 => 'Agosto',
                        9 => 'Septiembre',
                        10 => 'Octubre',
                        11 => 'Noviembre',
                        12 => 'Diciembre',
                    ])
                    ->required(),

                TextInput::make('año')
                    ->label('Año')
                    ->default(date('Y'))
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('descripcion')
                    ->label('Descripción')
                    ->maxLength(255),

                Checkbox::make('cerrada')
                    ->label('Nómina cerrada')
                    ->default(false)
                    ->hidden(true),


                // CheckboxList para empleados
                Repeater::make('empleadosSeleccionados')
                    ->label('Lista de empleados')
                    ->schema([
                        Checkbox::make('seleccionado')
                            ->label('Aplicar'),
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('salario')
                            ->label('Salario')
                            ->disabled()
                            ->dehydrated(),
                        \Filament\Forms\Components\Textarea::make('deducciones')
                            ->label('Deducciones')
                            ->disabled()
                            ->default(fn ($state) => $state),
                        \Filament\Forms\Components\Textarea::make('percepciones')
                            ->label('Percepciones')
                            ->disabled()
                            ->default(fn ($state) => $state),
                        TextInput::make('total')
                            ->label('Total')
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->default(function () {
                        return \App\Models\Empleado::with(['deduccionesAplicadas.deduccion', 'percepcionesAplicadas.percepcion'])->get()->map(function ($empleado) {
                            $salario = $empleado->salario;
                            $deduccionesArray = $empleado->deduccionesAplicadas->map(function ($relacion) use ($salario) {
                                $deduccion = $relacion->deduccion;
                                if (!$deduccion) return null;
                                $nombre = $deduccion->deduccion ?? '';
                                $tipo = trim(strtolower($deduccion->tipo_valor)) === 'porcentaje' ? 'Porcentaje' : 'Monto';
                                $valor = $tipo === 'Porcentaje' ? ($deduccion->valor . '%') : ('$' . $deduccion->valor);
                                return $nombre . ': ' . $valor;
                            })->filter()->values()->toArray();
                            $deduccionesTexto = implode("\n", $deduccionesArray);
                            $totalDeducciones = $empleado->deduccionesAplicadas->sum(function ($relacion) use ($salario) {
                                $deduccion = $relacion->deduccion;
                                if (!$deduccion) return 0;
                                if (trim(strtolower($deduccion->tipo_valor)) === 'porcentaje') {
                                    return ($salario * ($deduccion->valor / 100));
                                }
                                return $deduccion->valor;
                            });
                            $percepcionesArray = $empleado->percepcionesAplicadas->map(function ($relacion) {
                                $percepcion = $relacion->percepcion;
                                if (!$percepcion) return null;
                                $nombre = $percepcion->percepcion ?? '';
                                // Si es Horas Extras, mostrar cantidad y calcular monto
                                if ($nombre === 'Horas Extras') {
                                    $cantidad = $relacion->cantidad_horas ?? 0;
                                    $valorUnitario = $percepcion->valor ?? 0;
                                    $monto = $cantidad * $valorUnitario;
                                    return $nombre . ' (' . $cantidad . 'h): $' . number_format($monto, 2);
                                }
                                $valor = '$' . $percepcion->valor;
                                return $nombre . ': ' . $valor;
                            })->filter()->values()->toArray();
                            $percepcionesTexto = implode("\n", $percepcionesArray);
                            $totalPercepciones = $empleado->percepcionesAplicadas->sum(function ($relacion) {
                                $percepcion = $relacion->percepcion;
                                if (!$percepcion) return 0;
                                if (($percepcion->percepcion ?? '') === 'Horas Extras') {
                                    $cantidad = $relacion->cantidad_horas ?? 0;
                                    $valorUnitario = $percepcion->valor ?? 0;
                                    return $cantidad * $valorUnitario;
                                }
                                return $percepcion->valor ?? 0;
                            });
                            $total = $salario + $totalPercepciones - $totalDeducciones;
                            return [
                                'empleado_id' => $empleado->id,
                                'nombre' => $empleado->persona->primer_nombre . ' ' . $empleado->persona->primer_apellido,
                                'salario' => $salario,
                                'deducciones' => $deduccionesTexto,
                                'percepciones' => $percepcionesTexto,
                                'total' => $total,
                                'seleccionado' => false,
                            ];
                        })->toArray();
                    })
                    ->columns(6)
                    ->columnSpanFull()
                    ->disableItemDeletion()
                    ->disableItemCreation()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('descripcion')->label('Descripción'),
                Tables\Columns\TextColumn::make('mes')->label('Mes'),
                Tables\Columns\TextColumn::make('año')->label('Año'),
                Tables\Columns\TextColumn::make('empresa.nombre')->label('Empresa'),

                Tables\Columns\IconColumn::make('cerrada')
                    ->label('Cerrada')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),   
                Tables\Actions\EditAction::make()
                    ->visible(fn (Nominas $record): bool => !$record->cerrada),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Nominas $record): bool => !$record->cerrada),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNominas::route('/'),
            'create' => Pages\CreateNomina::route('/create'),
            'edit' => Pages\EditNomina::route('/{record}/edit'),
            'view' => Pages\ViewNomina::route('/{record}'),
        ];
    }
}
