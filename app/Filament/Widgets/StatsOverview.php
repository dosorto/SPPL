<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = OrdenCompras::query();
        if ($startDate) {
            $query->whereDate('fecha_realizada', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('fecha_realizada', '<=', $endDate);
        }

        $orderIds = $query->pluck('id');
        $totalProducts = OrdenComprasDetalle::whereIn('orden_compra_id', $orderIds)
            ->whereNull('deleted_at')
            ->sum('cantidad');

        return [
            Stat::make('Órdenes Totales', $query->count())
                ->description('Número total de órdenes')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('primary'),
            Stat::make('Órdenes Recibidas', $query->where('estado', 'Recibida')->count())
                ->description('Órdenes en inventario')
                ->descriptionIcon('heroicon-o-inbox')
                ->color('success'),
            Stat::make('Órdenes Pendientes', $query->where('estado', 'Pendiente')->count())
                ->description('Órdenes abiertas')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Productos Totales', $totalProducts ?? 0)
                ->description('Cantidad total de productos')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),
        ];
    }
}