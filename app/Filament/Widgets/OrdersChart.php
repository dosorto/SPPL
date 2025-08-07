<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Órdenes y Productos por Fecha';
    protected static string $chartType = 'line';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = OrdenCompras::select([
            DB::raw('DATE(fecha_realizada) as date'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('COALESCE(SUM(orden_compras_detalles.cantidad), 0) as product_count')
        ])
            ->leftJoin('orden_compras_detalles', 'orden_compras.id', '=', 'orden_compras_detalles.orden_compra_id')
            ->whereNull('orden_compras.deleted_at')
            ->where('empresa_id', 6) // Ajusta según tu lógica, esto parece venir de un filtro
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($startDate) {
            $query->whereDate('fecha_realizada', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('fecha_realizada', '<=', $endDate);
        }

        $orders = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes',
                    'data' => $orders->pluck('order_count')->toArray(),
                    'borderColor' => '#4F46E5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Productos',
                    'data' => $orders->pluck('product_count')->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $orders->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('d/m/Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}