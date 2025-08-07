<x-filament-panels::page :show-header="false"> <!-- Desactiva la cabecera predeterminada -->
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-600">
        <!-- Cabecera Personalizada -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Panel de Control</h1>
            <div class="flex items-center space-x-4">
                <x-filament::avatar :user="auth()->user()" size="lg" />
                <div>
                    <p class="text-lg text-gray-700 dark:text-gray-300">
                        {{ __('Bienvenido, :name', ['name' => auth()->user()->name]) }}
                    </p>
                    <x-filament::button :href="filament()->getLogoutUrl()" color="danger" size="sm" class="mt-2">
                        {{ __('Cerrar Sesión') }}
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        @if ($this->hasFiltersForm())
            <div class="mb-6">
                <x-filament::form wire:model="filters">
                    {{ $this->filtersForm }}
                </x-filament::form>
            </div>
        @endif

        <!-- Widgets -->
        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            @foreach ($this->getHeaderWidgets() as $widget)
                <x-filament::widget :widget="$widget" />
            @endforeach
        </div>

        <!-- Enlaces Personalizados -->
        
</x-filament-panels::page>