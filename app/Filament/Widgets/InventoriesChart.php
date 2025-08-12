<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\InventarioInsumos;
use App\Models\InventarioProductos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoriesChart extends ChartWidget
{
    protected static ?string $heading = 'Inventario de Insumos y Productos por Fecha';
    protected static string $chartType = 'line';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        // Consulta para Inventario de Insumos
        $queryInsumos = InventarioInsumos::select([
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as insumo_count'),
            DB::raw('COALESCE(SUM(cantidad), 0) as insumo_product_count')
        ])
            ->whereNull('deleted_at')
            ->where('empresa_id', 6) // Ajusta según tu lógica
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($startDate) {
            $queryInsumos->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $queryInsumos->whereDate('created_at', '<=', $endDate);
        }

        $insumos = $queryInsumos->get();

        // Consulta para Inventario de Productos
        $queryProductos = InventarioProductos::select([
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as producto_count'),
            DB::raw('COALESCE(SUM(cantidad), 0) as producto_product_count')
        ])
            ->whereNull('deleted_at')
            ->where('empresa_id', 6) // Ajusta según tu lógica
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($startDate) {
            $queryProductos->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $queryProductos->whereDate('created_at', '<=', $endDate);
        }

        $productos = $queryProductos->get();

        // Combinar fechas únicas
        $dates = collect([])
            ->merge($insumos->pluck('date'))
            ->merge($productos->pluck('date'))
            ->unique()
            ->sort()
            ->values();

        // Mapear datos para cada fecha
        $insumoCounts = $dates->map(fn ($date) => $insumos->firstWhere('date', $date)?->insumo_count ?? 0)->toArray();
        $insumoProductCounts = $dates->map(fn ($date) => $insumos->firstWhere('date', $date)?->insumo_product_count ?? 0)->toArray();
        $productoCounts = $dates->map(fn ($date) => $productos->firstWhere('date', $date)?->producto_count ?? 0)->toArray();
        $productoProductCounts = $dates->map(fn ($date) => $productos->firstWhere('date', $date)?->producto_product_count ?? 0)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Insumos',
                    'data' => $insumoCounts,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Productos en Insumos',
                    'data' => $insumoProductCounts,
                    'borderColor' => '#D97706',
                    'backgroundColor' => 'rgba(217, 119, 6, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Productos Inventario',
                    'data' => $productoCounts,
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Productos en Inventario',
                    'data' => $productoProductCounts,
                    'borderColor' => '#B91C1C',
                    'backgroundColor' => 'rgba(185, 28, 28, 0.2)',
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