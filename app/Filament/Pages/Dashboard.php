<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\InventoriesChart;
use App\Filament\Widgets\StatsOverview;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Panel de Control Principal';
    protected static ?string $navigationIcon = 'heroicon-s-chart-pie';
    protected static ?string $navigationLabel = 'Dashboard';

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'xl' => 2,
        ];
    }

    /**
     * Unificamos todos los widgets aquí para evitar duplicados
     */
    public function getWidgets(): array
    {
        return [
            StatsOverview::make(['columnSpan' => 'full']),
            OrdersChart::make(['columnSpan' => ['sm' => 1, 'md' => 2, 'xl' => 2]]),
            InventoriesChart::make(['columnSpan' => ['sm' => 1, 'md' => 2, 'xl' => 2]]),
        ];
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtros del Panel')
                    ->description('Filtra los datos por rango de fechas.')
                    ->aside()
                    ->collapsible()
                    ->compact()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Fecha de Inicio')
                            ->placeholder('Selecciona la fecha inicial')
                            ->displayFormat('d/m/Y')
                            ->default(now()->startOfYear())
                            ->prefixIcon('heroicon-o-calendar')
                            ->native(false),
                        DatePicker::make('endDate')
                            ->label('Fecha de Fin')
                            ->placeholder('Selecciona la fecha final')
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->prefixIcon('heroicon-o-calendar')
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title;
    }

    public function getHeading(): string|Htmlable
    {
        return 'Bienvenido a tu Panel de Control';
    }

    public function getSubheading(): string|Htmlable
    {
        return 'Visualiza métricas clave y tendencias de órdenes e inventarios.';
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.pages.dashboard-header', [
            'title' => $this->getHeading(),
            'subheading' => $this->getSubheading(),
        ]);
    }
}
