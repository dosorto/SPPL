<x-filament-widgets::widget>
    <x-filament::section>
        {{-- 
            Diseño simple y elegante con enfoque minimalista
            - py-16: Padding vertical generoso para respiración
            - Centrado perfecto con flexbox
        --}}
        <div class="py-16 px-6">
            <div class="max-w-sm mx-auto text-center space-y-6">
                
                {{-- Icono con diseño minimalista --}}
                <div class="mx-auto w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center shadow-sm border border-gray-200 dark:border-gray-700">
                    <svg class="w-8 h-8 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>

                {{-- Título limpio y directo --}}
                <div class="space-y-2">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Caja cerrada
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Apertura una caja para comenzar a facturar
                    </p>
                </div>

                {{-- Botón de acción simple --}}
                <div class="pt-2">
                    {{ $this->getAperturarCajaAction() }}
                </div>

            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>