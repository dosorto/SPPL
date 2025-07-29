<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $factura->numero_factura }}</title>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    <style>
        @media print {
            .no-print { display: none; }
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="text-sm text-gray-800">
    <div class="max-w-3xl mx-auto bg-white p-6">
        <!-- Encabezado -->
        <div class="text-center border-b border-gray-300 pb-4 mb-4">
            <h1 class="text-xl font-bold">{{ $factura->empresa->nombre }}</h1>
            <p>{{ $factura->empresa->direccion }}</p>
            <p>R.T.N: {{ $factura->empresa->rtn }}</p>
            <p>Tel: {{ $factura->empresa->telefono }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
            <div>
                <p><strong>Factura:</strong> {{ $factura->numero_factura }}</p>
                <p><strong>Fecha:</strong> {{ $factura->fecha_factura->format('d/m/Y') }}</p>
                <p><strong>Estado:</strong> {{ $factura->estado }}</p>
            </div>
            <div>
                <p><strong>Cliente:</strong> {{ $factura->cliente->persona->primer_nombre }} {{ $factura->cliente->persona->primer_apellido }}</p>
                <p><strong>Vendedor:</strong> {{ $factura->empleado->persona->primer_nombre }} {{ $factura->empleado->persona->primer_apellido }}</p>
                <p><strong>Categoría:</strong> {{ $factura->cliente->categoriaCliente->nombre ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- CAI -->
        <div class="grid grid-cols-3 gap-4 border-t border-b py-2 text-xs mb-4">
            <div><strong>CAI:</strong><br>{{ $factura->cai->cai ?? 'N/A' }}</div>
            <div><strong>Rango Autorizado:</strong><br>{{ $factura->cai?->rango_inicial ?? 'N/A' }} - {{ $factura->cai?->rango_final ?? 'N/A' }}</div>
            <div><strong>Fecha Límite:</strong><br>{{ $factura->cai?->fecha_limite_emision?->format('d/m/Y') ?? 'N/A' }}</div>
        </div>

        <!-- Detalles -->
        <table class="w-full border-collapse mb-4">
            <thead class="bg-gray-100 text-xs">
                <tr>
                    <th class="border px-2 py-1 text-left">Producto</th>
                    <th class="border px-2 py-1 text-right">Cant</th>
                    <th class="border px-2 py-1 text-right">P/U</th>
                    <th class="border px-2 py-1 text-right">Desc (%)</th>
                    <th class="border px-2 py-1 text-right">ISV (%)</th>
                    <th class="border px-2 py-1 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($factura->detalles as $detalle)
                    <tr>
                        <td class="border px-2 py-1">{{ $detalle->producto->producto->nombre }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                        <td class="border px-2 py-1 text-right">L. {{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($detalle->descuento_aplicado ?? 0, 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($detalle->isv_aplicado ?? 0, 2) }}</td>
                        <td class="border px-2 py-1 text-right">L. {{ number_format($detalle->sub_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="flex justify-end space-y-1 text-sm mb-6">
            <div class="w-1/2">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>L. {{ number_format($factura->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Impuestos:</span>
                    <span>L. {{ number_format($factura->impuestos, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold border-t pt-1">
                    <span>Total:</span>
                    <span>L. {{ number_format($factura->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Pagos -->
        <div class="mb-4">
            <h2 class="text-md font-semibold mb-2">Pagos:</h2>
            <table class="w-full border-collapse text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-2 py-1 text-left">Método</th>
                        <th class="border px-2 py-1 text-right">Monto</th>
                        <th class="border px-2 py-1 text-right">Recibido</th>
                        <th class="border px-2 py-1 text-right">Cambio</th>
                        <th class="border px-2 py-1 text-left">Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($factura->pagos as $pago)
                        <tr>
                            <td class="border px-2 py-1">{{ $pago->metodoPago->nombre }}</td>
                            <td class="border px-2 py-1 text-right">L. {{ number_format($pago->monto, 2) }}</td>
                            <td class="border px-2 py-1 text-right">L. {{ number_format($pago->monto_recibido ?? 0, 2) }}</td>
                            <td class="border px-2 py-1 text-right">L. {{ number_format($pago->cambio ?? 0, 2) }}</td>
                            <td class="border px-2 py-1">{{ $pago->referencia ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs mt-6 border-t pt-2">
            <p>Gracias por su compra</p>
        </div>
    </div>
</body>
</html>
