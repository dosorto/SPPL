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
                        TextInput::make('deducciones')
                            ->label('Deducciones')
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->default(function () {
                        return \App\Models\Empleado::with('deduccionesAplicadas.deduccion')->get()->map(function ($empleado) {
                            $totalDeducciones = $empleado->deduccionesAplicadas->sum(function ($relacion) {
                                return $relacion->deduccion->valor ?? 0;
                                });
                            return [
                                'empleado_id' => $empleado->id,
                                'nombre' => $empleado->persona->primer_nombre . ' ' . $empleado->persona->primer_apellido,
                                'salario' => $empleado->salario,
                                'deducciones' => $totalDeducciones,
                                'seleccionado' => false,
                            ];
                        })->toArray();
                    })
                    ->columns(4)
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
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNominas::route('/'),
            'create' => Pages\CreateNomina::route('/create'),
            'edit' => Pages\EditNomina::route('/{record}/edit'),
        ];
    }
}
