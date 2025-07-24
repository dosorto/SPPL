<?php

namespace App\Filament\Resources;

// No necesitamos importar el Enum
use App\Filament\Resources\FacturaResource\Pages;
use App\Models\Factura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions;
use Illuminate\Database\Eloquent\Builder;


class FacturaResource extends Resource
{
    protected static ?string $model = Factura::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Ventas';

    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->options(
                                \App\Models\Cliente::with('persona')
                                    ->get()
                                    ->mapWithKeys(function ($cliente) {
                                        return [
                                            $cliente->id => $cliente->persona->primer_nombre . ' ' . $cliente->persona->primer_apellido,
                                        ];
                                    })
                            )
                            ->disabled(),

                        Forms\Components\Select::make('empleado_id')
                            ->label('Vendedor')
                            ->options(
                                \App\Models\Empleado::with('persona')
                                    ->get()
                                    ->mapWithKeys(function ($empleado) {
                                        return [
                                            $empleado->id => $empleado->persona->primer_nombre . ' ' . $empleado->persona->primer_apellido,
                                        ];
                                    })
                            )
                            ->disabled(),

                        Forms\Components\DatePicker::make('fecha_factura')
                            ->disabled(),
                        // CAMBIO: Se le dan las opciones directamente como un array.
                        Forms\Components\Select::make('estado')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Pagada' => 'Pagada',
                                'Anulada' => 'Anulada',
                                'Vencida' => 'Vencida',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Totales')
                    ->schema([
                        
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('L.')
                            ->disabled(),
                        Forms\Components\TextInput::make('impuestos')
                            ->numeric()
                            ->prefix('L.')
                            ->disabled(),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('L.')
                            ->disabled(),
                        
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('N° Factura')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cliente.persona.primer_nombre')->label('Cliente'),
                Tables\Columns\TextColumn::make('empleado.persona.primer_nombre')->label('Vendedor'),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    // CAMBIO: Se usan los strings directamente para los colores.
                    ->color(fn (string $state): string => match ($state) {
                        'Pendiente' => 'warning',
                        'Pagada' => 'success',
                        'Anulada' => 'danger',
                        'Vencida' => 'gray',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('total')->money('HNL')->sortable(),
                Tables\Columns\TextColumn::make('fecha_factura')->date('d/m/Y')->sortable(),
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
            'index' => Pages\ListFacturas::route('/'),
            'edit' => Pages\EditFactura::route('/{record}/edit'),
            
            // --- AÑADE ESTAS DOS LÍNEAS ---
            'generar-factura' => Pages\GenerarFactura::route('/generar'),
            'view' => Pages\ViewFactura::route('/{record}'), // La necesitarás más tarde
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['cliente.persona', 'empleado.persona']);
    }
    

}
