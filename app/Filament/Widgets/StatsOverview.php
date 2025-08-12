<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;
use App\Models\InventarioInsumos;
use App\Models\InventarioProductos;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        // Órdenes de Compra
        $queryOrdenes = OrdenCompras::where('empresa_id', 6); // Ajusta según tu lógica
        if ($startDate) {
            $queryOrdenes->whereDate('fecha_realizada', '>=', $startDate);
        }
        if ($endDate) {
            $queryOrdenes->whereDate('fecha_realizada', '<=', $endDate);
        }

        $orderIds = $queryOrdenes->pluck('id');
        $totalProductosOrdenes = OrdenComprasDetalle::whereIn('orden_compra_id', $orderIds)
            ->whereNull('deleted_at')
            ->sum('cantidad');

        // Inventario de Insumos
        $queryInsumos = InventarioInsumos::where('empresa_id', 6); // Ajusta según tu lógica
        if ($startDate) {
            $queryInsumos->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $queryInsumos->whereDate('created_at', '<=', $endDate);
        }

        $totalProductosInsumos = $queryInsumos->sum('cantidad');

        // Inventario de Productos
        $queryProductos = InventarioProductos::where('empresa_id', 6); // Ajusta según tu lógica
        if ($startDate) {
            $queryProductos->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $queryProductos->whereDate('created_at', '<=', $endDate);
        }

        $totalProductosInventario = $queryProductos->sum('cantidad');

        return [
            Stat::make('Órdenes Totales', $queryOrdenes->count())
                ->description('Número total de órdenes')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('primary'),
            Stat::make('Órdenes Recibidas', $queryOrdenes->where('estado', 'Recibida')->count())
                ->description('Órdenes en inventario')
                ->descriptionIcon('heroicon-o-inbox')
                ->color('success'),
            Stat::make('Órdenes Pendientes', $queryOrdenes->where('estado', 'Pendiente')->count())
                ->description('Órdenes abiertas')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Productos en Órdenes', $totalProductosOrdenes ?? 0)
                ->description('Cantidad total de productos en órdenes')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),
            Stat::make('Insumos Totales', $queryInsumos->count())
                ->description('Número total de insumos')
                ->descriptionIcon('heroicon-o-beaker')
                ->color('primary'),
            Stat::make('Productos en Insumos', $totalProductosInsumos ?? 0)
                ->description('Cantidad total de productos en insumos')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),
            Stat::make('Productos Inventario Totales', $queryProductos->count())
                ->description('Número total de productos en inventario')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('primary'),
            Stat::make('Productos en Inventario', $totalProductosInventario ?? 0)
                ->description('Cantidad total de productos en inventario')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),
        ];
    }
}