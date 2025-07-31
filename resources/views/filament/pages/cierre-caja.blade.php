<x-filament-panels::page>
    @if ($apertura)
        {{-- 
            ============================================================================
            == ¡IMPORTANTE! AJUSTA ESTA LÍNEA SEGÚN TUS DATOS ==
            ============================================================================
            Asegúrate de que la llave usada aquí ('Efectivo') sea IDÉNTICA a la que
            aparece en tu "Desglose de Ventas". Podría ser 'efectivo', 'contado', etc.
        --}}
        @php
            $ventasEnEfectivo = $reporte['Efectivo'] ?? 0;
            $totalEnCajaEsperado = $apertura->monto_inicial + $ventasEnEfectivo;
        @endphp

        <x-filament::card>
            {{-- Sección del Encabezado Principal --}}
            <div class="mb-8 border-b border-gray-200 pb-6 dark:border-gray-700">
                <h2 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Resumen de Cierre de Caja
                </h2>
                <p class="mt-2 text-md text-gray-500 dark:text-gray-400">
                    Caja abierta por <span class="font-semibold">{{ $apertura->user->name }}</span> el {{ $apertura->fecha_apertura->format('d/m/Y \a \l\a\s h:i A') }}
                </p>
            </div>

            {{-- Grid principal con distribución asimétrica para enfocar en los totales --}}
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-5">

                {{-- Columna Izquierda: Desglose de Ventas --}}
                <div class="space-y-6 lg:col-span-2">
                    <div class="flex items-center gap-4">
                        <div class="rounded-lg bg-primary-500/10 p-2 text-primary-600 dark:text-primary-400">
                             <x-heroicon-o-receipt-percent class="h-6 w-6" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Desglose de Ventas
                        </h3>
                    </div>

                    <div class="space-y-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                        @forelse ($reporte as $metodo => $total)
                            <div class="flex items-center justify-between font-mono text-sm">
                                <span class="text-gray-600 dark:text-gray-400">{{ ucfirst($metodo) }}:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">L {{ number_format($total, 2) }}</span>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-6">
                                <x-heroicon-o-circle-stack class="mx-auto h-8 w-8 text-gray-400"/>
                                <p class="mt-2 text-sm">No se registraron ventas.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Columna Derecha: Totales Financieros --}}
                <div class="space-y-6 lg:col-span-3">
                     <div class="flex items-center gap-4">
                        <div class="rounded-lg bg-success-500/10 p-2 text-success-600 dark:text-success-400">
                             <x-heroicon-o-calculator class="h-6 w-6" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Cálculo de Caja
                        </h3>
                    </div>
                    
                    <div class="space-y-4">
                        {{-- Desglose para el cálculo final --}}
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                            <dl class="space-y-2">
                                <div class="flex items-center justify-between text-base">
                                    <dt class="text-gray-600 dark:text-gray-400">Monto Inicial en Caja:</dt>
                                    <dd class="font-mono font-medium text-gray-900 dark:text-white">L {{ number_format($apertura->monto_inicial, 2) }}</dd>
                                </div>
                                <div class="flex items-center justify-between text-base">
                                    <dt class="text-gray-600 dark:text-gray-400">(+) Ventas en Efectivo:</dt>
                                    <dd class="font-mono font-medium text-blue-600 dark:text-blue-500">L {{ number_format($ventasEnEfectivo, 2) }}</dd>
                                </div>
                            </dl>
                        </div>
                        
                        {{-- Tarjeta de Total Final (Elemento Héroe) --}}
                        <div class="rounded-xl bg-gradient-to-br from-success-50 via-success-100 to-emerald-100 p-6 text-center dark:from-success-900/50 dark:via-success-900/80 dark:to-emerald-950/90">
                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                Total Esperado en Caja
                            </p>
                            <p class="mt-2 text-4xl font-extrabold tracking-tight text-success-600 dark:text-success-400">
                                L {{ number_format($totalEnCajaEsperado, 2) }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                (Monto Inicial + Ventas en Efectivo)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::card>
    @else
        {{-- Diseño para cuando la caja no se encuentra --}}
        <x-filament::card>
            <div class="flex flex-col items-center justify-center space-y-4 py-12 text-center">
                <div class="rounded-full bg-primary-100 p-4 text-primary-600 dark:bg-gray-800 dark:text-primary-400">
                    <x-heroicon-o-inbox class="h-12 w-12" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    No hay una caja activa
                </h3>
                <p class="max-w-md text-gray-500 dark:text-gray-400">
                    Parece que no hay ninguna caja abierta en este momento. Por favor, realiza una apertura de caja para poder ver el resumen de cierre.
                </p>
            </div>
        </x-filament::card>
    @endif
</x-filament-panels::page>