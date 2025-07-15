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

class EmpleadoDeduccionesResource extends Resource
{
    protected static ?string $model = EmpleadoDeducciones::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->required(),

                Select::make('deduccion_id')
                    ->label('Deducción')
                    ->relationship('deduccion', 'deduccion')
                    ->required(),

                DatePicker::make('fecha_aplicacion')
                    ->label('Fecha de Aplicación')
                    ->default(now())
                    ->required(),
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

                TextColumn::make('fecha_aplicacion')
                    ->label('Fecha de aplicación')
                    ->date('d-m-Y') 
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
