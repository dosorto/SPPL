<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasInsumos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = '📦 Productos vs ⚗️ Insumos — Órdenes por Fecha';
    protected static string $chartType = 'line';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;
        $estado    = $this->filters['estado'] ?? null;

        // -----------------------------
        // 📦 ÓRDENES DE PRODUCTOS
        // -----------------------------
        $queryOrdenesProductos = OrdenCompras::select([
                DB::raw('DATE(fecha_realizada) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COALESCE(SUM(orden_compras_detalles.cantidad), 0) as order_product_count')
            ])
            ->leftJoin('orden_compras_detalles', 'orden_compras.id', '=', 'orden_compras_detalles.orden_compra_id')
            ->whereNull('orden_compras.deleted_at')
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($startDate) $queryOrdenesProductos->whereDate('fecha_realizada', '>=', $startDate);
        if ($endDate)   $queryOrdenesProductos->whereDate('fecha_realizada', '<=', $endDate);
        if ($estado)    $queryOrdenesProductos->where('estado', $estado);

        $ordenesProductos = $queryOrdenesProductos->get();

        // -----------------------------
        // ⚗️ ÓRDENES DE INSUMOS
        // -----------------------------
        $queryOrdenesInsumos = OrdenComprasInsumos::select([
                DB::raw('DATE(fecha_realizada) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COALESCE(SUM(orden_compras_insumos_detalles.cantidad), 0) as order_insumo_product_count')
            ])
            ->leftJoin('orden_compras_insumos_detalles', 'orden_compras_insumos.id', '=', 'orden_compras_insumos_detalles.orden_compra_insumo_id')
            ->whereNull('orden_compras_insumos.deleted_at')
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($startDate) $queryOrdenesInsumos->whereDate('fecha_realizada', '>=', $startDate);
        if ($endDate)   $queryOrdenesInsumos->whereDate('fecha_realizada', '<=', $endDate);
        if ($estado)    $queryOrdenesInsumos->where('estado', $estado);

        $ordenesInsumos = $queryOrdenesInsumos->get();

        // -----------------------------
        // Fechas únicas combinadas
        // -----------------------------
        $dates = collect([])
            ->merge($ordenesProductos->pluck('date'))
            ->merge($ordenesInsumos->pluck('date'))
            ->unique()
            ->sort()
            ->values();

        // -----------------------------
        // Mapear datos por fecha
        // -----------------------------
        $productosOrderCounts   = $dates->map(fn ($date) => $ordenesProductos->firstWhere('date', $date)?->order_count ?? 0)->toArray();
        $productosProductCounts = $dates->map(fn ($date) => $ordenesProductos->firstWhere('date', $date)?->order_product_count ?? 0)->toArray();

        $insumosOrderCounts     = $dates->map(fn ($date) => $ordenesInsumos->firstWhere('date', $date)?->order_count ?? 0)->toArray();
        $insumosProductCounts   = $dates->map(fn ($date) => $ordenesInsumos->firstWhere('date', $date)?->order_insumo_product_count ?? 0)->toArray();

        return [
            'datasets' => [
                [
                    'label' => '📦 Órdenes de Productos',
                    'data' => $productosOrderCounts,
                    'borderColor' => '#4F46E5', // azul
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => '📦 Productos en Órdenes',
                    'data' => $productosProductCounts,
                    'borderColor' => '#10B981', // verde
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => '⚗️ Órdenes de Insumos',
                    'data' => $insumosOrderCounts,
                    'borderColor' => '#F59E0B', // naranja
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => '⚗️ Insumos en Órdenes',
                    'data' => $insumosProductCounts,
                    'borderColor' => '#D97706', // naranja oscuro
                    'backgroundColor' => 'rgba(217, 119, 6, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $dates->map(fn ($date) => Carbon::parse($date)->format('d/m/Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
