<div>
    <div class="grid grid-cols-3 gap-4 mb-4" x-data="{ isDisabled: @entangle('isBasicInfoComplete').defer }">
        <div>
            <label class="block text-sm font-medium text-gray-700">Producto</label>
            <input 
                type="text" 
                wire:model.live="producto_nombre" 
                list="productoOptions" 
                placeholder="Escribe SKU o nombre para buscar o selecciona..." 
                class="w-full border rounded p-2" 
                wire:change="updateProductoId"
            >
            <datalist id="productoOptions">
                @foreach ($productos as $id => $nombre)
                    <option value="{{ $nombre }}" data-id="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </datalist>
            @error('producto_id') 
                <span class="text-red-600 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Cantidad</label>
            <input type="number" wire:model.defer="cantidad" class="w-full border rounded p-2" min="1" :disabled="isDisabled">
            @error('cantidad') 
                <span class="text-red-600 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Precio (Lps)</label>
            <input type="number" wire:model.defer="precio" class="w-full border rounded p-2" min="0" step="0.01" :disabled="isDisabled">
            @error('precio') 
                <span class="text-red-600 text-sm">{{ $message }}</span> 
            @enderror
        </div>
    </div>

    <button
        type="button"
        wire:click="addProducto"
        class="bg-gray-600 hover:bg-blue-700 text-black font-semibold px-5 py-2 rounded-lg mb-4 shadow-md transition duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="isDisabled"
    >
        {{ isset($editIndex) && $editIndex !== null ? 'Actualizar Producto' : 'Añadir a Tabla' }}
    </button>

    @if (count($detalles))
        <table class="w-full table-auto border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2">Producto</th>
                    <th class="px-4 py-2">Cantidad</th>
                    <th class="px-4 py-2">Precio</th>
                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalles as $index => $item)
                    <tr>
                        <td class="border px-4 py-2">{{ $item['nombre_producto'] }}</td>
                        <td class="border px-4 py-2">{{ $item['cantidad'] }}</td>
                        <td class="border px-4 py-2">Lps {{ number_format($item['precio'], 2) }}</td>
                        <td class="border px-4 py-2">Lps {{ number_format($item['cantidad'] * $item['precio'], 2) }}</td>
                        <td class="border px-4 py-2">
                            <div class="flex space-x-2">
                                <button
                                    wire:click="editDetalle({{ $index }})"
                                    type="button"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition"
                                >
                                    ✏️ Editar
                                </button>

                                <button
                                    wire:click="removeDetalle({{ $index }})"
                                    type="button"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-md shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                                >
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500 mt-2">No hay productos añadidos.</p>
    @endif
</div>
