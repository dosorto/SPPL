<div>
    <div class="grid grid-cols-3 gap-4 mb-4" x-data="{ isDisabled: !@js($isBasicInfoComplete ?? false) }">
        <div>
            <label class="block text-sm font-medium text-gray-700">Producto</label>
            <select wire:model.live="producto_id" class="w-full border rounded p-2" :disabled="isDisabled">
                <option value="">Seleccione</option>
                @foreach ($productos as $id => $nombre)
                    <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>
            @error('producto_id') 
                <span class="text-red-600 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Cantidad</label>
            <input type="number" wire:model.live="cantidad" class="w-full border rounded p-2" min="1" :disabled="isDisabled">
            @error('cantidad') 
                <span class="text-red-600 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Precio (Lps)</label>
            <input type="number" wire:model.live="precio" class="w-full border rounded p-2" min="0" step="0.01" :disabled="isDisabled">
            @error('precio') 
                <span class="text-red-600 text-sm">{{ $message }}</span> 
            @enderror
        </div>
    </div>

   <button
    type="button"
    wire:click="addProducto"
    class="bg-blue-600 hover:bg-blue-700 text-blue-200 font-semibold px-5 py-2 rounded-lg mb-4 shadow-md transition duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
    :disabled="!@js($isBasicInfoComplete ?? false)">
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
                            <button wire:click="editDetalle({{ $index }})" class="text-blue-600 hover:underline mr-2">Editar</button>
                            <button wire:click="removeDetalle({{ $index }})" class="text-red-600 hover:underline">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500 mt-2">No hay productos añadidos.</p>
    @endif
</div>
