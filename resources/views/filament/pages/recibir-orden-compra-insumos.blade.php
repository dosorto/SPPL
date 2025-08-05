<x-filament::page>
    <x-filament::card>
        <h2 class="text-xl font-bold">Recibir Orden de Compra Insumos #{{ $orden->id }}</h2>
        <p><strong>Proveedor:</strong> {{ $orden->proveedor->nombre_proveedor }}</p>
        <p><strong>Fecha:</strong> {{ $orden->fecha_realizada->format('d/m/Y') }}</p>
        <p><strong>Estado:</strong> {{ $orden->estado }}</p>

        <h3 class="mt-4 text-lg font-semibold">Análisis de Calidad</h3>
        <p><strong>Grasa:</strong> {{ $orden->porcentaje_grasa ?? 'N/A' }}%</p>
        <p><strong>Proteína:</strong> {{ $orden->porcentaje_proteina ?? 'N/A' }}%</p>
        <p><strong>Humedad:</strong> {{ $orden->porcentaje_humedad ?? 'N/A' }}%</p>
        <p><strong>Anomalías:</strong> {{ $orden->anomalias ? 'Sí' : 'No' }}</p>
        @if($orden->anomalias)
            <p><strong>Detalles de Anomalías:</strong> {{ $orden->detalles_anomalias }}</p>
        @endif

        <h3 class="mt-4 text-lg font-semibold">Detalles</h3>
        <table class="w-full mt-2 border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">Producto</th>
                    <th class="p-2 border">Cantidad</th>
                    <th class="p-2 border">Precio Unitario</th>
                    <th class="p-2 border">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orden->detalles as $detalle)
                    <tr>
                        <td class="p-2 border">{{ $detalle->producto->nombre }}</td>
                        <td class="p-2 border">{{ $detalle->cantidad }}</td>
                        <td class="p-2 border">{{ number_format($detalle->precio_unitario, 2) }} HNL</td>
                        <td class="p-2 border">{{ number_format($detalle->subtotal, 2) }} HNL</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($orden->estado === 'Pendiente')
            <x-filament::button wire:click="recibir" color="success" class="mt-4">
                Recibir en Inventario
            </x-filament::button>
        @else
            <x-filament::badge color="success" class="mt-4">
                Orden ya recibida en inventario
            </x-filament::badge>
        @endif
    </x-filament::card>
</x-filament::page>
