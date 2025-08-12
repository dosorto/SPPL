<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;
use App\Models\OrdenComprasInsumos;
use App\Models\OrdenComprasInsumosDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Órdenes de Compra y Órdenes de Insumos por Fecha';
    protected static string $chartType = 'line';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        // Consulta para Órdenes de Compra
        $queryOrdenes = OrdenCompras::select([
            DB::raw('DATE(fecha_realizada) as date'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('COALESCE(SUM(orden_compras_detalles.cantidad), 0) as order_product_count')
        ])
            ->leftJoin('orden_compras_detalles', 'orden_compras.id', '=', 'orden_compras_detalles.orden_compra_id')
            ->whereNull('orden_compras.deleted_at')
            ->where('orden_compras.empresa_id', 6) // Ajusta según tu lógica
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($startDate) {
            $queryOrdenes->whereDate('fecha_realizada', '>=', $startDate);
        }
        if ($endDate) {
            $queryOrdenes->whereDate('fecha_realizada', '<=', $endDate);
        }

        $ordenes = $queryOrdenes->get();

        // Consulta para Órdenes de Compra de Insumos
        $queryOrdenesInsumos = OrdenComprasInsumos::select([
            DB::raw('DATE(fecha_realizada) as date'),
            DB::raw('COUNT(*) as order_insumo_count'),
            DB::raw('COALESCE(SUM(orden_compras_insumos_detalles.cantidad), 0) as order_insumo_product_count')
        ])
            ->leftJoin('orden_compras_insumos_detalles', 'orden_compras_insumos.id', '=', 'orden_compras_insumos_detalles.orden_compra_insumo_id')
            ->whereNull('orden_compras_insumos.deleted_at')
            ->where('orden_compras_insumos.empresa_id', 6) // Ajusta según tu lógica
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($startDate) {
            $queryOrdenesInsumos->whereDate('fecha_realizada', '>=', $startDate);
        }
        if ($endDate) {
            $queryOrdenesInsumos->whereDate('fecha_realizada', '<=', $endDate);
        }

        $ordenesInsumos = $queryOrdenesInsumos->get();

        // Combinar fechas únicas
        $dates = collect([])
            ->merge($ordenes->pluck('date'))
            ->merge($ordenesInsumos->pluck('date'))
            ->unique()
            ->sort()
            ->values();

        // Mapear datos para cada fecha
        $orderCounts = $dates->map(fn ($date) => $ordenes->firstWhere('date', $date)?->order_count ?? 0)->toArray();
        $orderProductCounts = $dates->map(fn ($date) => $ordenes->firstWhere('date', $date)?->order_product_count ?? 0)->toArray();
        $orderInsumoCounts = $dates->map(fn ($date) => $ordenesInsumos->firstWhere('date', $date)?->order_insumo_count ?? 0)->toArray();
        $orderInsumoProductCounts = $dates->map(fn ($date) => $ordenesInsumos->firstWhere('date', $date)?->order_insumo_product_count ?? 0)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes de Compra',
                    'data' => $orderCounts,
                    'borderColor' => '#4F46E5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Productos en Órdenes de Compra',
                    'data' => $orderProductCounts,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Órdenes de Insumos',
                    'data' => $orderInsumoCounts,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Productos en Órdenes de Insumos',
                    'data' => $orderInsumoProductCounts,
                    'borderColor' => '#D97706',
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