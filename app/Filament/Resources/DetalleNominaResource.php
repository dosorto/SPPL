<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetalleNominaResource\Pages;
use App\Filament\Resources\DetalleNominaResource\RelationManagers;
use App\Models\DetalleNominas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\NumberColumn;

class DetalleNominaResource extends Resource
{
    protected static ?string $model = DetalleNominas::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                // Empleado (nombre completo, usando la relación)
                Forms\Components\TextInput::make('empleado.nombre_completo')
                    ->label('Empleado')
                    
                    ->disabled(),

                // Sueldo bruto
                Forms\Components\TextInput::make('sueldo_bruto')
                    ->label('Sueldo Bruto')
                    ->disabled(),

                // Deducciones
                Forms\Components\TextInput::make('deducciones')
                    ->label('Deducciones')
                    ->disabled(),

                // Total horas extra
                Forms\Components\TextInput::make('total_horas_extra')
                    ->label('Total de Horas Extra')
                    ->disabled(),

                // Monto de horas extra
                Forms\Components\TextInput::make('horas_extra_monto')
                    ->label('Monto por Horas Extra')
                    ->disabled(),

                // Sueldo neto
                Forms\Components\TextInput::make('sueldo_neto')
                    ->label('Sueldo Neto')
                    ->disabled(),
            ]);

        }

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('empleado.nombre_completo')
                ->label('Empleado')
                ->sortable()
                ->searchable(),

            TextColumn::make('sueldo_bruto')
                ->label('Bruto')
                ->money('HNL'), // si la versión lo soporta

            TextColumn::make('deducciones')
                ->label('Deducciones')
                ->money('HNL'),

            TextColumn::make('sueldo_neto')
                ->label('Neto')
                ->money('HNL'),
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


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetalleNominas::route('/'),
            'create' => Pages\CreateDetalleNomina::route('/create'),
            'edit' => Pages\EditDetalleNomina::route('/{record}/edit'),
            'view' => Pages\ViewDetalleNomina::route('/{record}'),
        ];
    }
}
