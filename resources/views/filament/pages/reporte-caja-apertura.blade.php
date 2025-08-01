<x-filament-panels::page>
    {{-- Header con información de la caja --}}
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Caja Cerrada</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Reporte completo del día</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Usuario</div>
                    <div class="font-medium text-gray-900 dark:text-white">{{ $record->user->name }}</div>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Apertura</div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ $record->fecha_apertura->format('d/m/Y H:i:s') }}
                </div>
            </div>
            <div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Cierre</div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ $record->fecha_cierre?->format('d/m/Y H:i:s') ?? 'N/A' }}
                </div>
            </div>
            <div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Duración</div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                    @if($record->fecha_cierre)
                        {{ $record->fecha_apertura->diffForHumans($record->fecha_cierre, true) }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Grid principal con los datos --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Ventas del día --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <h3 class="font-medium text-gray-900 dark:text-white">Ventas del Día</h3>
                </div>
            </div>
            
            <div class="p-4 space-y-3">
                {{-- Monto inicial --}}
                <div class="flex items-center justify-between py-2 px-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-green-100 dark:bg-green-900/40 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-green-800 dark:text-green-200">Monto Inicial</span>
                    </div>
                    <span class="font-mono font-semibold text-green-800 dark:text-green-200">
                        L {{ number_format($record->monto_inicial, 2) }}
                    </span>
                </div>

                {{-- Ventas por método --}}
                @foreach ($reporteSistema as $metodo => $total)
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex items-center gap-2">
                            @if($metodo === 'Efectivo')
                                <div class="w-6 h-6 bg-yellow-100 dark:bg-yellow-900/30 rounded flex items-center justify-center">
                                    <svg class="w-3 h-3 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2"/>
                                    </svg>
                                </div>
                            @elseif($metodo === 'Tarjeta')
                                <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded flex items-center justify-center">
                                    <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-6 h-6 bg-purple-100 dark:bg-purple-900/30 rounded flex items-center justify-center">
                                    <svg class="w-3 h-3 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01"/>
                                    </svg>
                                </div>
                            @endif
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ventas {{ $metodo }}</span>
                        </div>
                        <span class="font-mono font-semibold text-gray-900 dark:text-white">
                            L {{ number_format($total, 2) }}
                        </span>
                    </div>
                @endforeach

                {{-- Total de ventas --}}
                <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-3">
                    <div class="flex items-center justify-between py-2 px-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <span class="text-sm font-semibold text-blue-800 dark:text-blue-200">Total Ventas</span>
                        <span class="font-mono font-bold text-blue-900 dark:text-blue-100">
                            L {{ number_format(array_sum($reporteSistema), 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Conteo de efectivo --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-orange-50 dark:bg-orange-900/20">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                    </svg>
                    <h3 class="font-medium text-gray-900 dark:text-white">Conteo de Efectivo</h3>
                </div>
            </div>
            
            <div class="p-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 px-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Esperado en Caja</span>
                        <span class="font-mono font-semibold text-blue-900 dark:text-blue-100">
                            L {{ number_format($totalEnCajaEsperado, 2) }}
                        </span>
                    </div>

                    @if($conteoUsuario)
                        <div class="flex items-center justify-between py-2 px-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Contado por Usuario</span>
                            <span class="font-mono font-semibold text-yellow-900 dark:text-yellow-100">
                                L {{ number_format(array_sum($conteoUsuario), 2) }}
                            </span>
                        </div>

                        {{-- Diferencia --}}
                        @php
                            $diferencia = array_sum($conteoUsuario) - $totalEnCajaEsperado;
                        @endphp
                        <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ $diferencia == 0 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                            <span class="text-sm font-medium {{ $diferencia == 0 ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                                Diferencia
                            </span>
                            <span class="font-mono font-bold {{ $diferencia == 0 ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100' }}">
                                L {{ number_format($diferencia, 2) }}
                                @if($diferencia > 0)
                                    <span class="text-xs">(Sobrante)</span>
                                @elseif($diferencia < 0)
                                    <span class="text-xs">(Faltante)</span>
                                @endif
                            </span>
                        </div>

                        {{-- Detalle del conteo --}}
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-3">
                            <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Detalle del Conteo</h4>
                            <div class="space-y-1">
                                @foreach($conteoUsuario as $denominacion => $cantidad)
                                    @if($cantidad > 0)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">{{ $denominacion }}</span>
                                            <span class="font-mono text-gray-900 dark:text-white">{{ $cantidad }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                            <em>No se realizó conteo manual</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Notas del cierre --}}
    @if($notasCierre)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                    </svg>
                    <h3 class="font-medium text-gray-900 dark:text-white">Notas del Cierre</h3>
                </div>
            </div>
            <div class="p-4">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $notasCierre }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Resumen final --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700">
        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Resumen del Día</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        L {{ number_format(array_sum($reporteSistema), 2) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Facturado</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        L {{ number_format($totalEnCajaEsperado, 2) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Efectivo Esperado</div>
                </div>
                <div class="text-center">
                    @if($conteoUsuario)
                        @php $diferencia = array_sum($conteoUsuario) - $totalEnCajaEsperado; @endphp
                        <div class="text-2xl font-bold {{ $diferencia == 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            L {{ number_format($diferencia, 2) }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Diferencia</div>
                    @else
                        <div class="text-2xl font-bold text-gray-400">N/A</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Sin Conteo</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>