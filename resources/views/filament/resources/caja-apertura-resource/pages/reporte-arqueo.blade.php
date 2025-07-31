@extends('filament::page')

@section('content')
    <h2 class="text-xl font-bold mb-4">Reporte de Arqueo de Caja</h2>
    <div class="mb-4">
        <strong>Caja:</strong> {{ $cajaApertura->caja->nombre }}<br>
        <strong>Usuario:</strong> {{ $cajaApertura->usuario->name ?? '' }}<br>
        <strong>Monto Inicial:</strong> L. {{ number_format($cajaApertura->monto_inicial, 2) }}<br>
        <strong>Fecha Apertura:</strong> {{ $cajaApertura->fecha_apertura }}<br>
        <strong>Fecha Cierre:</strong> {{ $cajaApertura->fecha_cierre }}<br>
    </div>
    <h3 class="text-lg font-semibold mb-2">Totales por Método de Pago</h3>
    <ul class="mb-4">
        @foreach($totalesPorMetodo as $metodo => $total)
            <li><strong>{{ $metodo }}:</strong> L. {{ number_format($total, 2) }}</li>
        @endforeach
    </ul>
    <h3 class="text-lg font-semibold mb-2">Facturas</h3>
    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2">N° Factura</th>
                <th class="py-2">Cliente</th>
                <th class="py-2">Total</th>
                <th class="py-2">Método de Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facturas as $factura)
                <tr>
                    <td class="py-2">{{ $factura->id }}</td>
                    <td class="py-2">{{ $factura->cliente->persona->primer_nombre ?? '' }}</td>
                    <td class="py-2">L. {{ number_format($factura->total, 2) }}</td>
                    <td class="py-2">{{ $factura->metodo_pago ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
