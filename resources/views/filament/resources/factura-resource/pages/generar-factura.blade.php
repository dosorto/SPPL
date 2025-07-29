<x-filament-panels::page>

    {{-- FORMULARIO SUPERIOR --}}
    <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
        {{ $this->form }}
    </div>

    {{-- MOSTRAR CATEGORÍA DEL CLIENTE --}}
    @if ($categoriaClienteNombre)
        <div class="mt-4 p-4 bg-blue-50 border border-blue-300 text-blue-900 rounded-xl shadow-sm">
            <strong>Categoría del Cliente:</strong> {{ $categoriaClienteNombre }}
        </div>
    @endif


    {{-- TABLA DE PRODUCTOS AGREGADOS --}}
    <div class="mt-6">
        @if (!empty($lineasVenta))
            <div class="overflow-x-auto bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">SKU</th>
                            <th class="px-6 py-3">Producto</th>
                            <th class="px-6 py-3">Tipo de Precio</th>
                            <th class="px-6 py-3 text-right">Precio Unit.</th>
                            <th class="px-6 py-3 text-center">Cantidad</th>
                            <th class="px-6 py-3 text-right">Descuento (%)</th>
                            <th class="px-6 py-3 text-right">Subtotal</th>
                            <th class="px-6 py-3 text-right">ISV</th>
                            <th class="px-6 py-3 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lineasVenta as $productoId => $linea)
                            <tr wire:key="producto-{{ $productoId }}" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-6 py-4 font-mono">{{ $linea['sku'] }}</td>
                                <th class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $linea['nombre'] }}
                                </th>
                                <td class="px-6 py-4">
                                    <select
                                        wire:key="tipo-precio-{{ $productoId }}-{{ $linea['tipo_precio_key'] }}"
                                        wire:change="actualizarTipoPrecioLinea({{ $productoId }}, $event.target.value)"
                                        class="block w-full text-sm border-gray-300 rounded-lg ..."
                                    >
                                        <option value="precio_detalle" @selected($linea['tipo_precio_key'] === 'precio_detalle')>Detalle</option>
                                        <option value="precio_mayorista" @selected($linea['tipo_precio_key'] === 'precio_mayorista')>Mayorista</option>
                                        <option value="precio_promocion" @selected($linea['tipo_precio_key'] === 'precio_promocion')>Promoción</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-right">L. {{ number_format($linea['precio_unitario'], 2) }}</td>
                                <td class="px-6 py-4 text-center">{{ $linea['cantidad'] }}</td>

                                {{-- NUEVA COLUMNA: Descuento aplicado --}}
                                <td class="px-6 py-4 text-right">
                                    @if (($linea['descuento_aplicado'] ?? 0) > 0)
                                        <span class="text-green-600 font-semibold">{{ $linea['descuento_aplicado'] }}%</span>
                                    @else
                                        {{ $linea['descuento_aplicado'] ?? 0 }}%
                                    @endif
                                </td>
                                {{-- Subtotal --}}
                                <td class="px-6 py-4 text-right font-bold">
                                    L. {{ number_format($linea['precio_unitario'] * $linea['cantidad'], 2) }}
                                </td>

                                {{-- ISV --}}
                                <td class="px-6 py-4 text-right">
                                    L. {{ number_format(($linea['precio_unitario'] * $linea['cantidad']) * ($linea['isv_producto'] / 100), 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button wire:click="eliminarProducto({{ $productoId }})" class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- SECCIÓN DE TOTALES --}}
            <div class="flex justify-end mt-6">
                <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h5 class="mb-2 text-xl font-medium text-gray-900 dark:text-white">Resumen de la Venta</h5>
                    <ul class="space-y-3">
                        <li class="flex items-center justify-between">
                            <span class="text-base font-normal text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span class="text-base font-semibold text-gray-900 dark:text-white">L. {{ number_format($subtotal, 2) }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-base font-normal text-gray-500 dark:text-gray-400">Total ISV</span>
                            <span class="text-base font-semibold text-gray-900 dark:text-white">L. {{ number_format($impuestos, 2) }}</span>
                        </li>
                        <li class="flex items-center justify-between pt-2 mt-2 border-t border-gray-200 dark:border-gray-600">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">Total a Pagar</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">L. {{ number_format($total, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- BOTÓN FINAL PARA GENERAR LA FACTURA --}}
            <div class="flex justify-end mt-6">
                <x-filament-actions::actions :actions="$this->getFormActions()" />
            </div>
        @else
            {{-- MENSAJE CUANDO NO HAY PRODUCTOS --}}
            <div class="p-6 text-center bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <p class="text-gray-500 dark:text-gray-400">Aún no se han agregado productos a la venta.</p>
                <p class="text-sm text-gray-400 dark:text-gray-500">Use el formulario de arriba para comenzar.</p>
            </div>
        @endif
    </div>

</x-filament-panels::page>