<?php

namespace App\Filament\Resources\FacturaResource\Widgets;

use App\Filament\Pages\CierreCaja;
use App\Filament\Resources\FacturaResource;
use App\Models\CajaApertura;
use App\Models\Factura;
use Filament\Actions\Action; // Importar Action
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CajaStatsWidget extends BaseWidget
{
    // Hacemos que la vista del widget pueda acceder a los métodos de acción
    protected static string $view = 'filament.resources.factura-resource.widgets.caja-stats-widget';

    protected function getStats(): array
    {
        if (!Session::has('apertura_id')) return [];

        $aperturaId = Session::get('apertura_id');
        $apertura = CajaApertura::find($aperturaId);
        $facturas = Factura::where('apertura_id', $aperturaId);

        return [
            Stat::make('Fecha de Apertura', Carbon::parse($apertura->fecha_apertura)->format('d/m/Y h:i A'))
                ->description('Inicio de la sesión actual')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Número de Ventas', $facturas->count())
                ->description('Facturas en esta sesión')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Total Vendido', 'L. ' . number_format($facturas->sum('total'), 2))
                ->description('Monto acumulado')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),
        ];
    }

    /**
     * ¡NUEVO! Define la acción para generar factura.
     */
    public function getGenerarFacturaAction(): Action
    {
        return Action::make('generar_factura')
            ->label('Generar Factura')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->url(FacturaResource::getUrl('generar-factura'));
    }

    public function getCerrarCajaAction(): Action
    {
        return Action::make('cerrarCaja')
            ->label('Cerrar Caja / Arqueo')
            ->color('danger')
            ->icon('heroicon-o-lock-closed')
            ->url(CierreCaja::getUrl());
    }
}