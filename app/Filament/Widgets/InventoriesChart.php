<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\InventarioInsumos;
use App\Models\InventarioProductos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoriesChart extends ChartWidget
{
    protected static ?string $heading = '📦 Productos e Insumos — Inventario ';
    protected static string $chartType = 'line';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;

        // -----------------------------
        // ⚗️ Inventario de Insumos
        // -----------------------------
        $queryInsumos = InventarioInsumos::select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('COALESCE(SUM(cantidad), 0) as total_qty')
            ])
            ->whereNull('deleted_at') // TenantScoped ya filtra
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($startDate) $queryInsumos->whereDate('created_at', '>=', $startDate);
        if ($endDate)   $queryInsumos->whereDate('created_at', '<=', $endDate);

        $insumos = $queryInsumos->get();

        // -----------------------------
        // 📦 Inventario de Productos
        // -----------------------------
        $queryProductos = InventarioProductos::select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('COALESCE(SUM(cantidad), 0) as total_qty')
            ])
            ->whereNull('deleted_at')
            ->where('empresa_id', auth()->user()->empresa_id) // tenant manual
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($startDate) $queryProductos->whereDate('created_at', '>=', $startDate);
        if ($endDate)   $queryProductos->whereDate('created_at', '<=', $endDate);

        $productos = $queryProductos->get();

        // -----------------------------
        // Fechas únicas
        // -----------------------------
        $dates = collect([])
            ->merge($insumos->pluck('date'))
            ->merge($productos->pluck('date'))
            ->unique()
            ->sort()
            ->values();

        // -----------------------------
        // Mapear datos
        // -----------------------------
        $insumoCounts        = $dates->map(fn ($date) => $insumos->firstWhere('date', $date)?->count ?? 0)->toArray();
        $insumoQtys          = $dates->map(fn ($date) => $insumos->firstWhere('date', $date)?->total_qty ?? 0)->toArray();

        $productoCounts      = $dates->map(fn ($date) => $productos->firstWhere('date', $date)?->count ?? 0)->toArray();
        $productoQtys        = $dates->map(fn ($date) => $productos->firstWhere('date', $date)?->total_qty ?? 0)->toArray();

        return [
            'datasets' => [
                [
                    'label' => '📦 Productos Inventario',
                    'data' => $productoCounts,
                    'borderColor' => '#4F46E5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => '📦 Cantidad en Inventario Productos',
                    'data' => $productoQtys,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => '⚗️ Insumos Inventario',
                    'data' => $insumoCounts,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => '⚗️ Cantidad en Inventario Insumos',
                    'data' => $insumoQtys,
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
