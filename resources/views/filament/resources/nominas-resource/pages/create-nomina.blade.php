<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="create">
            <div class="mt-2 flex justify-end">
                <x-filament::button type="submit" color="primary" icon="heroicon-o-check-circle" class="font-bold">
                    Guardar Nómina
                </x-filament::button>
            </div>
            <!-- Información general de la nómina -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">Empresa</label>
                    <input type="text" class="block w-full mt-1 border-gray-300 rounded-md dark:bg-gray-700 dark:text-white" value="{{ $this->empresaNombre ?? '' }}" disabled />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">Mes</label>
                    <select wire:model.live="mes" class="block w-full mt-1 border-gray-300 rounded-md dark:bg-gray-700 dark:text-white">
                        <option value="">Seleccione un mes</option>
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                    @if(!empty($this->mostrarErrorMes) && empty($this->mes))
                        <div class="text-xs mt-1 font-semibold" style="color:#dc2626;">&#9888; Por favor seleccione un mes para la nómina.</div>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">Año</label>
                    <input type="text" class="block w-full mt-1 border-gray-300 rounded-md dark:bg-gray-700 dark:text-white" value="{{ $this->año ?? '' }}" disabled />
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Descripción</label>
                <input type="text" class="block w-full mt-1 mb-4 border-gray-300 rounded-md dark:bg-gray-700 dark:text-white" wire:model.lazy="descripcion" maxlength="255" />
                <div class="flex justify-end mt-6">
                    <x-filament::button 
                        type="button" 
                        color="primary" 
                        :icon="collect($this->empleadosSeleccionados)->every(fn($e) => !empty($e['seleccionado'])) ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'" 
                        class="font-bold" 
                        wire:click="toggleSeleccionTodos">
                        @if(collect($this->empleadosSeleccionados)->every(fn($e) => !empty($e['seleccionado'])))
                            Deseleccionar todos
                        @else
                            Seleccionar todos
                        @endif
                    </x-filament::button>
                </div>
            </div>

            <!-- Tabla de empleados -->
            <div class="bg-white shadow rounded-lg dark:bg-gray-800 w-full">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Empleados en Nómina</h3>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-auto">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-center text-base font-bold uppercase tracking-wider" style="color:#1e3a8a;">Seleccionar</th>
                                    <th class="px-4 py-3 text-left text-base font-bold uppercase tracking-wider" style="color:#1e3a8a;">Nombre</th>
                                    <th class="px-4 py-3 text-center text-base font-bold uppercase tracking-wider" style="color:#1e3a8a;">Salario</th>
                                    <th class="px-4 py-3 text-center text-base font-bold uppercase tracking-wider" style="color:#1e3a8a;">Deducciones</th>
                                    <th class="px-4 py-3 text-center text-base font-bold uppercase tracking-wider" style="color:#1e3a8a;">Percepciones</th>
                                    <th class="px-4 py-3 text-center text-base font-bold uppercase tracking-wider" style="color:#1e3a8a;">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach($this->empleadosSeleccionados as $index => $empleado)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 text-center">
                                        <input type="checkbox" wire:model.live="empleadosSeleccionados.{{ $index }}.seleccionado" />
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $empleado['nombre'] }}
                                    </td>
                                    <td class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                        L. {{ number_format($empleado['salario'], 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-center text-sm">
                                        <ul class="list-none p-0 m-0">
                                            @foreach($empleado['deduccionesArray'] as $dIndex => $deduccion)
                                            <li class="flex items-center gap-2">
                                                <input type="checkbox" wire:model.live="empleadosSeleccionados.{{ $index }}.deduccionesArray.{{ $dIndex }}.aplicada" />
                                                <span @if(!$deduccion['aplicada']) style="text-decoration: line-through; color: #888; font-weight: bold;" @endif>
                                                    {{ $deduccion['nombre'] }} ({{ $deduccion['valorMostrado'] }})
                                                </span>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="px-4 py-2 text-center text-sm">
                                        <ul class="list-none p-0 m-0">
                                            @foreach($empleado['percepcionesArray'] as $pIndex => $percepcion)
                                            <li class="flex items-center gap-2">
                                                <input type="checkbox" wire:model.live="empleadosSeleccionados.{{ $index }}.percepcionesArray.{{ $pIndex }}.aplicada" />
                                                <span @if(!$percepcion['aplicada']) style="text-decoration: line-through; color: #888; font-weight: bold;" @endif>
                                                    {{ $percepcion['nombre'] }} ({{ $percepcion['valorMostrado'] }})
                                                </span>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="px-4 py-2 text-center text-sm font-bold text-green-600 dark:text-green-400">
                                        L. {{ number_format($empleado['total'], 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
            </div>
        </form>
    </div>
</x-filament-panels::page>
