<x-filament::page>
    <div class="space-y-6">

        {{-- ENCABEZADO --}}
        <x-filament::card>
            <div class="grid md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Factura</p>
                    <p class="text-lg font-semibold">#{{ $record->numero_factura }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Fecha</p>
                    <p>{{ $record->fecha_factura->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Estado</p>
                    <x-filament::badge color="{{ $record->estado === 'Pagada' ? 'success' : 'warning' }}">
                        {{ $record->estado }}
                    </x-filament::badge>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Empresa</p>
                    <p>{{ $record->empresa->nombre ?? 'N/A' }}</p>
                </div>
            </div>

            @if ($record->cai)
                <div class="grid md:grid-cols-3 gap-4 mt-4 border-t pt-4 text-center">
                    <div>
                        <p class="text-sm text-gray-500">CAI</p>
                        <p class="break-all font-medium">{{ $record->cai->cai }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Vence</p>
                        <p class="font-medium">{{ $record->cai->fecha_limite_emision->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nomenclatura</p>
                        <p class="font-medium">
                            {{ $record->cai->establecimiento }}-
                            {{ $record->cai->punto_emision }}-
                            {{ $record->cai->tipo_documento }}-
                            {{ str_pad($record->cai->numero_actual, 8, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>
                </div>
            @endif

        </x-filament::card>

        {{-- CLIENTE Y VENDEDOR --}}
        <x-filament::card>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Cliente</p>
                    <p class="font-medium">
                        {{ $record->cliente->persona->primer_nombre }} {{ $record->cliente->persona->primer_apellido }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Vendedor</p>
                    <p class="font-medium">
                        {{ $record->empleado->persona->primer_nombre }} {{ $record->empleado->persona->primer_apellido }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Categoría del Cliente</p>
                    <p class="font-medium">
                        {{ $record->cliente->categoriaCliente->nombre ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </x-filament::card>

        {{-- DETALLE DE PRODUCTOS --}}
        <x-filament::card>
            <h3 class="text-lg font-bold mb-4">Detalle de Productos</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border rounded-lg">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Producto</th>
                            <th class="px-6 py-3">SKU</th>
                            <th class="px-6 py-3 text-right">Cantidad</th>
                            <th class="px-6 py-3 text-right">Precio Unitario</th>
                            <th class="px-6 py-3 text-right">Descuento (%)</th>
                            <th class="px-6 py-3 text-right">ISV (%)</th>
                            <th class="px-6 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($record->detalles as $detalle)
                            <tr class="border-b last:border-0">
                                <td class="px-6 py-3">{{ $detalle->producto->producto->nombre }}</td>
                                <td class="px-6 py-3">{{ $detalle->producto->producto->sku ?? 'N/A' }}</td>
                                <td class="px-6 py-3 text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                                <td class="px-6 py-3 text-right">
                                    L. {{ number_format($detalle->precio_unitario, 2) }}
                                    @if (($detalle->descuento_aplicado ?? 0) > 0)
                                        <span class="text-xs text-green-600 font-medium">(con descuento)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    {{ number_format($detalle->descuento_aplicado ?? 0, 2) }}%
                                </td>
                                <td class="px-6 py-3 text-right">
                                    {{ number_format($detalle->isv_aplicado ?? 0, 2) }}%
                                </td>
                                <td class="px-6 py-3 text-right">
                                    L. {{ number_format($detalle->sub_total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </x-filament::card>

        {{-- TOTALES --}}
        <x-filament::card>
            <div class="grid md:grid-cols-3 gap-4 text-right">
                <div>
                    <p class="text-sm text-gray-500">Subtotal</p>
                    <p class="text-base">L. {{ number_format($record->subtotal, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Impuestos</p>
                    <p class="text-base">L. {{ number_format($record->impuestos, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold">Total</p>
                    <p class="text-lg font-bold text-primary">L. {{ number_format($record->total, 2) }}</p>
                </div>
            </div>
        </x-filament::card>

        {{-- MÉTODOS DE PAGO --}}
        <x-filament::card>
            <h3 class="text-lg font-bold mb-4">Pagos Realizados</h3>
            @if ($record->pagos->isEmpty())
                <p class="text-gray-500">No se han registrado pagos.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border rounded-lg">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3">Método</th>
                                <th class="px-6 py-3 text-right">Monto</th>
                                <th class="px-6 py-3 text-right">Monto Recibido</th>
                                <th class="px-6 py-3 text-right">Cambio</th>
                                <th class="px-6 py-3">Referencia</th>
                                <th class="px-6 py-3">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($record->pagos as $pago)
                                <tr class="border-b last:border-0">
                                    <td class="px-6 py-3">{{ $pago->metodoPago->nombre }}</td>
                                    <td class="px-6 py-3 text-right">L. {{ number_format($pago->monto, 2) }}</td>
                                    <td class="px-6 py-3 text-right">L. {{ number_format($pago->monto_recibido ?? 0, 2) }}</td>
                                    <td class="px-6 py-3 text-right">L. {{ number_format($pago->cambio ?? 0, 2) }}</td>
                                    <td class="px-6 py-3">{{ $pago->referencia ?? 'N/A' }}</td>
                                    <td class="px-6 py-3">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::card>


    </div>
</x-filament::page>
