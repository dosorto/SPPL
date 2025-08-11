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
use Filament\Facades\Filament;

class DetalleNominaResource extends Resource
{
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        if (auth()->check()) {
            $user = auth()->user();
            
            // Si es root y tiene una empresa seleccionada en la sesión
            if ($user->hasRole('root') && session('current_empresa_id')) {
                $query->where('empresa_id', session('current_empresa_id'));
                
                \Illuminate\Support\Facades\Log::info('Filtrando DetalleNominas como root con empresa en sesión', [
                    'empresa_id' => session('current_empresa_id'),
                    'user_id' => $user->id,
                ]);
            }
            // Si no es root, filtrar por la empresa del usuario
            elseif (!$user->hasRole('root')) {
                $query->where('empresa_id', $user->empresa_id);
                
                \Illuminate\Support\Facades\Log::info('Filtrando DetalleNominas por empresa del usuario', [
                    'empresa_id' => $user->empresa_id,
                    'user_id' => $user->id,
                ]);
            }
            // Si es root pero no tiene empresa seleccionada, no aplicar filtro
            else {
                \Illuminate\Support\Facades\Log::info('Usuario root sin empresa seleccionada, mostrando todos los detalles de nóminas');
            }
        }
        return $query;
    }
    protected static ?string $model = DetalleNominas::class;

    protected static ?string $navigationGroup = 'Nominas';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Historial de Pagos';
    protected static ?string $modelLabel = 'Historial de Pago';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('nomina_id')
                    ->label('Nómina')
                    ->relationship('nomina', 'folio')
                    ->required(),

                \Filament\Forms\Components\Select::make('empleado_id')
                    ->label('Empleado')
                    ->relationship('empleado', 'nombre_completo')
                    ->required(),
                    
                \Filament\Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->relationship('empresa', 'nombre')
                    ->required()
                    ->default(fn () => session('current_empresa_id') ?? auth()->user()->empresa_id)
                    ->disabled(fn () => !\Filament\Facades\Filament::auth()->user()?->hasRole('root'))
                    ->dehydrated(true)
                    ->reactive()
                    ->live(),

                \Filament\Forms\Components\TextInput::make('sueldo_bruto')
                    ->label('Sueldo bruto')
                    ->numeric()
                    ->required(),

                \Filament\Forms\Components\TextInput::make('deducciones')
                    ->label('Deducciones')
                    ->numeric()
                    ->required(),

                \Filament\Forms\Components\TextInput::make('percepciones')
                    ->label('Percepciones')
                    ->numeric()
                    ->required(),

                \Filament\Forms\Components\TextInput::make('sueldo_neto')
                    ->label('Sueldo neto')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('empleado.nombre_completo')->label('Empleado'),
                \Filament\Tables\Columns\TextColumn::make('sueldo_bruto')->label('Sueldo Bruto'),
                \Filament\Tables\Columns\TextColumn::make('deducciones')->label('Deducciones'),
                \Filament\Tables\Columns\TextColumn::make('percepciones')->label('Percepciones'),
                \Filament\Tables\Columns\TextColumn::make('sueldo_neto')->label('Sueldo Neto'),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
            ])
            ->headerActions([]); // Oculta el botón de crear
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetalleNominas::route('/'),
            'edit' => Pages\EditDetalleNomina::route('/{record}/edit'),
            'view' => Pages\ViewDetalleNomina::route('/{record}'),
        ];
    }
    
    // Ocultar completamente la opción de crear nuevos registros
    public static function canCreate(): bool
    {
        return false;
    }
}
