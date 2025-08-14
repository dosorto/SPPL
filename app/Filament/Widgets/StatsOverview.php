<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;
use App\Models\InventarioInsumos;
use App\Models\InventarioProductos;
use App\Models\OrdenComprasInsumos;
use App\Models\OrdenComprasInsumosDetalle;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
{
    $startDate = $this->filters['startDate'] ?? null;
    $endDate   = $this->filters['endDate'] ?? null;

    // -----------------------------
    // 📦 PRODUCTOS
    // -----------------------------
    $queryOrdenesProductos = OrdenCompras::query();
    if ($startDate) $queryOrdenesProductos->whereDate('fecha_realizada', '>=', $startDate);
    if ($endDate)   $queryOrdenesProductos->whereDate('fecha_realizada', '<=', $endDate);

    $totalProductosEnOrdenes = OrdenComprasDetalle::whereIn(
        'orden_compra_id',
        $queryOrdenesProductos->pluck('id')
    )->sum('cantidad');

    $queryInventarioProductos = InventarioProductos::query();
    if ($startDate) $queryInventarioProductos->whereDate('created_at', '>=', $startDate);
    if ($endDate)   $queryInventarioProductos->whereDate('created_at', '<=', $endDate);
    $totalStockProductos = $queryInventarioProductos->sum('cantidad');

    // -----------------------------
    // ⚗️ INSUMOS
    // -----------------------------
    $queryOrdenesInsumos = OrdenComprasInsumos::query();
    if ($startDate) $queryOrdenesInsumos->whereDate('fecha_realizada', '>=', $startDate);
    if ($endDate)   $queryOrdenesInsumos->whereDate('fecha_realizada', '<=', $endDate);

    $totalInsumosEnOrdenes = OrdenComprasInsumosDetalle::whereIn(
        'orden_compra_insumo_id',
        $queryOrdenesInsumos->pluck('id')
    )->sum('cantidad');

    $queryInventarioInsumos = InventarioInsumos::query();
    if ($startDate) $queryInventarioInsumos->whereDate('created_at', '>=', $startDate);
    if ($endDate)   $queryInventarioInsumos->whereDate('created_at', '<=', $endDate);
    $totalStockInsumos = $queryInventarioInsumos->sum('cantidad');

    // -----------------------------
    // Retorno segmentado en dos bloques
    // -----------------------------
    return [
        // 📦 BLOQUE PRODUCTOS
        Stat::make('— 📦 Órdenes y Stock de Productos —', ''),

        Stat::make('Órdenes Productos', $queryOrdenesProductos->count())
            ->description('Total órdenes de productos')
            ->descriptionIcon('heroicon-o-shopping-cart')
            ->color('primary'),

        Stat::make('Recibidas', (clone $queryOrdenesProductos)->where('estado', 'Recibida')->count())
            ->description('Órdenes productos recibidas')
            ->descriptionIcon('heroicon-o-inbox')
            ->color('success'),

        Stat::make('Pendientes', (clone $queryOrdenesProductos)->where('estado', 'Pendiente')->count())
            ->description('Órdenes productos pendientes')
            ->descriptionIcon('heroicon-o-clock')
            ->color('warning'),

        Stat::make('En Órdenes', $totalProductosEnOrdenes)
            ->description('Cantidad productos en órdenes')
            ->descriptionIcon('heroicon-o-cube')
            ->color('info'),

        Stat::make('Stock Inventario', $totalStockProductos)
            ->description('Stock total productos')
            ->descriptionIcon('heroicon-o-archive-box')
            ->color('emerald'),

        // ⚗️ BLOQUE INSUMOS
        Stat::make('— ⚗️ Órdenes y Stock de Insumos —', ''),

        Stat::make('Órdenes Insumos', $queryOrdenesInsumos->count())
            ->description('Total órdenes de insumos')
            ->descriptionIcon('heroicon-o-shopping-bag')
            ->color('primary'),

        Stat::make('Recibidas', (clone $queryOrdenesInsumos)->where('estado', 'Recibida')->count())
            ->description('Órdenes insumos recibidas')
            ->descriptionIcon('heroicon-o-inbox')
            ->color('success'),

        Stat::make('Pendientes', (clone $queryOrdenesInsumos)->where('estado', 'Pendiente')->count())
            ->description('Órdenes insumos pendientes')
            ->descriptionIcon('heroicon-o-clock')
            ->color('warning'),

        Stat::make('En Órdenes', $totalInsumosEnOrdenes)
            ->description('Cantidad insumos en órdenes')
            ->descriptionIcon('heroicon-o-beaker')
            ->color('info'),

        Stat::make('Stock Inventario', $totalStockInsumos)
            ->description('Stock total insumos')
            ->descriptionIcon('heroicon-o-beaker')
            ->color('emerald'),
    ];
}

}
