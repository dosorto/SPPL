<table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
            <th class="px-4 py-2">Producto</th>
            <th class="px-4 py-2">Cantidad</th>
            <th class="px-4 py-2">Precio Unitario</th>
            <th class="px-4 py-2">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($detalles as $detalle)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-4 py-2">
                    {{ \App\Models\Productos::find($detalle['producto_id'])->nombre ?? 'Producto no encontrado' }}
                </td>
                <td class="px-4 py-2">{{ $detalle['cantidad'] }}</td>
                <td class="px-4 py-2">{{ number_format($detalle['precio'], 2) }} HNL</td>
                <td class="px-4 py-2">{{ number_format($detalle['cantidad'] * $detalle['precio'], 2) }} HNL</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-2 text-center">No hay productos agregados.</td>
            </tr>
        @endforelse
    </tbody>
</table>