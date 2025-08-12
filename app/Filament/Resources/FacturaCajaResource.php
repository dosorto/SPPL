<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacturaCajaResource\Pages;
use App\Models\Factura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FacturaCajaResource extends Resource
{
    protected static ?string $model = Factura::class;

    // --- Configuración del Menú (Este es el historial) ---
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Historial de Facturas';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?string $slug = 'historial-facturas';

    /**
     * ¡CAMBIO IMPORTANTE!
     * Copiamos la misma definición del formulario de FacturaResource
     * para que la página de edición sea idéntica.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->relationship('cliente.persona', 'primer_nombre') // Usamos relationship para eficiencia
                            ->label('Cliente')
                            ->disabled(),

                        Forms\Components\Select::make('empleado_id')
                            ->relationship('empleado.persona', 'primer_nombre') // Usamos relationship
                            ->label('Vendedor')
                            ->disabled(),

                        Forms\Components\DatePicker::make('fecha_factura')
                            ->disabled(),

                        // Este es el único campo editable
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
                        Forms\Components\TextInput::make('subtotal')->numeric()->prefix('L.')->disabled(),
                        Forms\Components\TextInput::make('impuestos')->numeric()->prefix('L.')->disabled(),
                        Forms\Components\TextInput::make('total')->numeric()->prefix('L.')->disabled(),
                    ])->columns(3),
            ]);
    }

    /**
     * La tabla ahora incluirá la acción de editar.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('N° Factura')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cliente.persona.primer_nombre')->label('Cliente'),
                Tables\Columns\TextColumn::make('empleado.persona.primer_nombre')->label('Vendedor'),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendiente' => 'warning',
                        'Pagada'    => 'success',
                        'Anulada'   => 'danger',
                        'Vencida'   => 'gray',
                        default     => 'primary',
                    }),
                Tables\Columns\TextColumn::make('total')->money('HNL')->sortable(),
                Tables\Columns\TextColumn::make('fecha_factura')->date('d/m/Y')->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // ¡CAMBIO IMPORTANTE! Se añade el botón de editar
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    /**
     * Registramos la página de edición.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturaCajas::route('/'),
            'view'  => Pages\ViewFacturaCaja::route('/{record}'),
            // ¡CAMBIO IMPORTANTE! Se añade la ruta para la página de edición
            'edit'  => Pages\EditFacturaCaja::route('/{record}/edit'),
        ];
    }

    /**
     * El historial no debe permitir crear nuevas facturas desde aquí.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Nos aseguramos de que no haya filtros y se muestren todas las facturas.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['cliente.persona', 'empleado.persona']);
    }
}