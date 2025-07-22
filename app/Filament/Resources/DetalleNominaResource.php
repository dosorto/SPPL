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
                \Filament\Forms\Components\View::make('filament.detalle-nomina.nombre-empleado')
                    ->label('Empleado'),

                Forms\Components\TextInput::make('sueldo_bruto')
                    ->label('Sueldo Bruto')
                    ->disabled(),

                Forms\Components\TextInput::make('percepciones')
                    ->label('Percepciones')
                    ->disabled(),

                Forms\Components\TextInput::make('deducciones')
                    ->label('Deducciones')
                    ->disabled(),

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
                ->money('HNL'),

            TextColumn::make('percepciones')
                ->label('Percepciones')
                ->money('HNL'),

            TextColumn::make('deducciones')
                ->label('Deducciones')
                ->money('HNL'),

            TextColumn::make('sueldo_neto')
                ->label('Neto')
                ->money('HNL'),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
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
