<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CajaAperturaResource\Pages;
use App\Models\CajaApertura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CajaAperturaResource extends Resource
{
    protected static ?string $model = CajaApertura::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?string $navigationLabel = 'Apertura de Caja';
    protected static ?string $modelLabel = 'Apertura de Caja';
    protected static ?string $pluralModelLabel = 'Aperturas de Caja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Apertura')
                    ->schema([
                        Forms\Components\Select::make('caja_id')
                            ->label('Caja')
                            ->relationship('caja', 'nombre') // Ajusta el campo visible
                            ->searchable()
                            ->required(),

                        Forms\Components\Hidden::make('empresa_id')
                            ->default(fn () => auth()->user()?->empresa_id),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => auth()->id()),

                        Forms\Components\TextInput::make('monto_inicial')
                            ->label('Monto Inicial')
                            ->prefix('L.')
                            ->numeric()
                            ->required(),

                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'abierta' => 'Abierta',
                                'cerrada' => 'Cerrada',
                            ])
                            ->required()
                            ->default('abierta'),

                        Forms\Components\DateTimePicker::make('fecha_apertura')
                            ->label('Fecha de Apertura')
                            ->default(now())
                            ->required(),

                        Forms\Components\DateTimePicker::make('fecha_cierre')
                            ->label('Fecha de Cierre')
                            ->nullable(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('caja.nombre')->label('Caja')->searchable(),
                Tables\Columns\TextColumn::make('usuario.name')->label('Usuario')->searchable(),
                Tables\Columns\TextColumn::make('monto_inicial')->label('Monto Inicial')->money('HNL'),
                Tables\Columns\TextColumn::make('estado')->badge()->color(fn (string $state) => match ($state) {
                    'abierta' => 'success',
                    'cerrada' => 'gray',
                    default => 'secondary',
                }),
                Tables\Columns\TextColumn::make('fecha_apertura')->label('Apertura')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('fecha_cierre')->label('Cierre')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCajaAperturas::route('/'),
            'create' => Pages\CreateCajaApertura::route('/create'),
            'edit' => Pages\EditCajaApertura::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['caja', 'usuario']);
    }
}
