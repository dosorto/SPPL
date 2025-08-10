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
    protected static ?string $navigationGroup = 'Nominas';
    protected static ?string $model = Nominas::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
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

                Select::make('tipo_pago')
                    ->label('Tipo de Pago')
                    ->options([
                        'mensual' => 'Mensual',
                        'quincenal' => 'Quincenal',
                        'semanal' => 'Semanal',
                    ])
                    ->required()
                    ->default('mensual'),

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
                    ->required()
                    ->minItems(1)
                    ->rule(function ($get) {
                        $empleados = $get('empleadosSeleccionados') ?? [];
                        $haySeleccionado = collect($empleados)->contains(function ($empleado) {
                            return isset($empleado['seleccionado']) && $empleado['seleccionado'];
                        });
                        return $haySeleccionado
                            ? null
                            : function ($attribute, $value, $fail) {
                                $fail('Debe seleccionar al menos un empleado para crear la nómina.');
                            };
                    })
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
                        // Deducciones con botón X para tachar
                        Forms\Components\Repeater::make('deduccionesArray')
                            ->label('Deducciones')
                            ->disableItemMovement()
                            ->schema([
                                Forms\Components\Toggle::make('aplicada')
                                    ->label(fn ($get) => $get('nombre'))
                                    ->onIcon('heroicon-s-check')
                                    ->offIcon('heroicon-s-x-mark')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->inline(true)
                                    ->live()
                                    ->extraAttributes(function ($get) {
                                        return $get('aplicada') ? [] : ['style' => 'text-decoration: line-through; color: #888; font-weight: bold;'];
                                    })
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $deducciones = $get('../../deduccionesArray');
                                        $totalDeducciones = 0;
                                        foreach ($deducciones as $deduccion) {
                                            if (isset($deduccion['aplicada']) && $deduccion['aplicada']) {
                                                $totalDeducciones += isset($deduccion['valorCalculado']) ? $deduccion['valorCalculado'] : 0;
                                            }
                                        }
                                        $salario = floatval($get('../../salario'));
                                        $percepciones = $get('../../percepcionesArray') ?? [];
                                        $totalPercepciones = 0;
                                        foreach ($percepciones as $p) {
                                            $totalPercepciones += isset($p['valorCalculado']) ? $p['valorCalculado'] : 0;
                                        }
                                        $total = $salario + $totalPercepciones - $totalDeducciones;
                                        $set('../../total', $total);
                                    }),
                                Forms\Components\Hidden::make('valorCalculado'),
                            ])
                            ->disableItemCreation()
                            ->disableItemDeletion(),
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
                                $tipo = trim(strtolower($deduccion->tipo_valor));
                                $valorCalculado = $tipo === 'porcentaje' ? ($salario * ($deduccion->valor / 100)) : $deduccion->valor;
                                return [
                                    'id' => $deduccion->id,
                                    'nombre' => $deduccion->deduccion ?? '',
                                    'tipo' => $tipo,
                                    'valor' => $deduccion->valor,
                                    'aplicada' => true,
                                    'valorMostrado' => $tipo === 'porcentaje' ? rtrim(rtrim($deduccion->valor, '0'), '.') . '%' : 'L' . number_format($deduccion->valor, 2),
                                    'valorCalculado' => $valorCalculado,
                                ];
                            })->filter()->values()->toArray();
                            $percepcionesArray = $empleado->percepcionesAplicadas->map(function ($relacion) {
                                $percepcion = $relacion->percepcion;
                                if (!$percepcion) return null;
                                $nombre = $percepcion->percepcion ?? '';
                                if ($nombre === 'Horas Extras') {
                                    $cantidad = $relacion->cantidad_horas ?? 0;
                                    $valorUnitario = $percepcion->valor ?? 0;
                                    $monto = $cantidad * $valorUnitario;
                                    return [
                                        'nombre' => $nombre . ' (' . $cantidad . 'h)',
                                        'valorMostrado' => $monto,
                                        'valorCalculado' => $monto,
                                    ];
                                }
                                $tipo = trim(strtolower($percepcion->tipo_valor ?? '')) === 'porcentaje' ? 'Porcentaje' : 'Monto';
                                $valor = $tipo === 'Porcentaje' ? ($percepcion->valor . '%') : $percepcion->valor;
                                return [
                                    'nombre' => $nombre,
                                    'valorMostrado' => $valor,
                                    'valorCalculado' => $percepcion->valor ?? 0,
                                ];
                            })->filter()->values()->toArray();
                            $percepcionesTexto = collect($percepcionesArray)->map(function ($item) {
                                return $item['nombre'] . ': ' . $item['valorMostrado'];
                            })->implode("\n");
                            $totalDeducciones = collect($deduccionesArray)->sum(function ($item) { return $item['aplicada'] ? $item['valorCalculado'] : 0; });
                            $totalPercepciones = collect($percepcionesArray)->sum('valorCalculado');
                            $total = $salario + $totalPercepciones - $totalDeducciones;
                            return [
                                'empleado_id' => $empleado->id,
                                'nombre' => $empleado->persona->primer_nombre . ' ' . $empleado->persona->primer_apellido,
                                'salario' => $salario,
                                'deduccionesArray' => $deduccionesArray,
                                'percepcionesArray' => $percepcionesArray,
                                'percepciones' => $percepcionesTexto,
                                'total' => $total,
                                'seleccionado' => true,
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
                Tables\Columns\TextColumn::make('año')->label('Año'),
                Tables\Columns\TextColumn::make('mes')
                    ->label('Mes')
                    ->formatStateUsing(function ($state) {
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                        ];
                        return $meses[(int)$state] ?? $state;
                    }),
                Tables\Columns\TextColumn::make('tipo_pago')
                    ->label('Tipo de Pago')
                    ->formatStateUsing(function ($state) {
                        $tipos = [
                            'mensual' => 'Mensual',
                            'quincenal' => 'Quincenal', 
                            'semanal' => 'Semanal',
                        ];
                        return $tipos[$state] ?? ucfirst($state);
                    }),
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
                Tables\Actions\Action::make('generarPdf')
                    ->label('Generar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn (Nominas $record) => route('nominas.generar-pdf', ['nomina' => $record->id]))
                    ->openUrlInNewTab(),
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
