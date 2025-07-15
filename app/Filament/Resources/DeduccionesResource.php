<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeduccionesResource\Pages;
use App\Filament\Resources\DeduccionesResource\RelationManagers;
use App\Models\Deducciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

class DeduccionesResource extends Resource
{
    protected static ?string $model = Deducciones::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            TextInput::make('deduccion')
                ->label('Nombre de la deducción')
                ->required(),

            TextInput::make('valor')
                ->label('Monto')
                ->numeric()
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('deduccion')->label('Nombre'),
                TextColumn::make('valor')->label('Monto'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListDeducciones::route('/'),
            'create' => Pages\CreateDeducciones::route('/create'),
            'edit' => Pages\EditDeducciones::route('/{record}/edit'),
        ];
    }
}
