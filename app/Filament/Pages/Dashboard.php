<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    //protected static ?string $title = 'Panel de Control';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    //protected static string $view = 'filament.pages.dashboard';

    // Desactiva la cabecera predeterminada (opcional, depende de la versión)
    protected static bool $shouldRegisterNavigation = true;
    

    public function getColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\OrdersChart::class,
        ];
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtros')
                    ->schema([
                        DatePicker::make('startDate')->label('Fecha Inicio')->displayFormat('d/m/Y'),
                        DatePicker::make('endDate')->label('Fecha Fin')->displayFormat('d/m/Y'),
                    ])
                    ->columns(2),
            ]);
    }
}