<x-filament::page>
    <div
        x-data="{
            openConfirm: false,
            usarCai: @entangle('data.usar_cai'),
            esOrden: @entangle('data.es_orden_compra'),
        }"
        x-on:keydown.escape.window="openConfirm = false"
    >
        <x-filament::card class="max-w-screen-lg mx-auto">
            {{ $this->form }}

            @php
                $acciones = collect($this->getCachedFormActions())
                    ->reject(fn ($a) => $a->getName() === 'submit');

                $f = $this->getRecord();
                $pagado = $f->pagos()->sum('monto');
                $saldo  = number_format(max(0, round($f->total - $pagado, 2)), 2);
            @endphp

            <div class="mt-6 flex justify-end gap-3">
                <x-filament-actions::actions :actions="$acciones" alignment="right" />

                <x-filament::button
                    color="success"
                    icon="heroicon-o-credit-card"
                    x-on:click="openConfirm = true"
                >
                    Registrar Pago
                </x-filament::button>
            </div>
        </x-filament::card>

        {{-- MODAL --}}
        <div x-cloak x-show="openConfirm" x-trap.noscroll="openConfirm"
             class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-gray-950/50" x-on:click="openConfirm = false"></div>

            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900"
                 x-transition.scale.origin.center>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40">
                    <x-filament::icon icon="heroicon-o-credit-card" class="h-7 w-7 text-green-600 dark:text-green-400" />
                </div>

                <h2 class="mt-4 text-center text-lg font-semibold">Confirmar registro de pago</h2>

                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-300">
                    Se registrará el pago para esta
                    <span class="font-semibold"
                          x-text="usarCai ? 'factura' : (esOrden ? 'orden de compra' : 'operación')"></span>.
                    <br>
                    Saldo a cubrir: <span class="font-semibold">L. {{ $saldo }}</span>
                </p>

                {{-- Mensaje si no eligió ningún toggle --}}
                <template x-if="!usarCai && !esOrden">
                    <p class="mt-2 text-center text-xs text-amber-600">
                        Debes seleccionar si emitir Factura (con CAI) u Orden de Compra (sin CAI).
                    </p>
                </template>

                {{-- BOTONES CENTRADOS --}}
                <div class="mt-6 flex justify-center gap-3">
                    <x-filament::button color="gray" x-on:click="openConfirm = false">
                        Cancelar
                    </x-filament::button>

                    <x-filament::button
                        color="success"
                        icon="heroicon-o-check-circle"
                        wire:click="registrarPago"
                        x-on:click="openConfirm = false"
                        wire:loading.attr="disabled"
                        x-bind:disabled="!usarCai && !esOrden"
                    >
                        Sí, confirmar
                    </x-filament::button>
                </div>
            </div>
        </div>

        <x-filament-actions::modals />
    </div>
</x-filament::page>
