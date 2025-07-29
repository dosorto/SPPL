<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.3; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .border-top { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; }
        td, th { padding: 3px; }
    </style>
</head>
<body>
    <div class="text-center">
        <strong>{{ $factura->empresa->nombre }}</strong><br>
        {{ $factura->empresa->direccion ?? '' }}<br>
        R.T.N: {{ $factura->empresa->rtn ?? 'N/A' }}<br>
        Tel: {{ $factura->empresa->telefono ?? '' }}
    </div>

    <div class="border-top"></div>

    <table>
        <tr>
            <td><strong>Factura:</strong> {{ $factura->numero_factura }}</td>
            <td><strong>Fecha:</strong> {{ $factura->fecha_factura->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Cliente:</strong> {{ $factura->cliente->persona->primer_nombre }} {{ $factura->cliente->persona->primer_apellido }}</td>
        </tr>
        <tr>
            <td><strong>Vendedor:</strong> {{ $factura->empleado->persona->primer_nombre }}</td>
            <td><strong>Estado:</strong> {{ $factura->estado }}</td>
        </tr>
    </table>

    <div class="border-top"></div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-right">Cant</th>
                <th class="text-right">P/U</th>
                <th class="text-right">Sub</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto->producto->nombre }}</td>
                    <td class="text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                    <td class="text-right">{{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="text-right">{{ number_format($detalle->sub_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-top"></div>

    <table>
        <tr>
            <td><strong>Subtotal</strong></td>
            <td class="text-right">L. {{ number_format($factura->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td><strong>ISV</strong></td>
            <td class="text-right">L. {{ number_format($factura->impuestos, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td class="text-right"><strong>L. {{ number_format($factura->total, 2) }}</strong></td>
        </tr>
    </table>

    <div class="border-top"></div>

    <p><strong>Pagos:</strong></p>
    <table>
        @foreach($factura->pagos as $pago)
            <tr>
                <td>{{ $pago->metodoPago->nombre }}</td>
                <td class="text-right">L. {{ number_format($pago->monto, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="border-top"></div>

    <p><strong>CAI:</strong> {{ $factura->cai->cai }}</p>
    <p><strong>Rango Autorizado:</strong> {{ $factura->cai->rango_inicial }} al {{ $factura->cai->rango_final }}</p>
    <p><strong>Fecha límite:</strong> {{ $factura->cai->fecha_limite_emision->format('d/m/Y') }}</p>

    <div class="text-center" style="margin-top: 20px;">
        <p>¡Gracias por su compra!</p>
    </div>
</body>
</html>

