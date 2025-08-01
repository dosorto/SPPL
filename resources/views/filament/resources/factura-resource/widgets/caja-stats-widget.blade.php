<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Esta parte se encarga de mostrar las 3 tarjetas de estadísticas --}}
        <div class="fi-wi-stats-overview grid gap-6 md:grid-cols-3">
            @foreach ($this->getStats() as $stat)
                {{ $stat }}
            @endforeach
        </div>

        {{-- Este contenedor pondrá los botones debajo de las estadísticas --}}
        <div class="mt-6 flex justify-center gap-4">
            {{-- Aquí se renderizan los botones que definimos en la clase del widget --}}
            {{ $this->getGenerarFacturaAction() }}
            {{ $this->getCerrarCajaAction() }}
        </div>

    </x-filament::section>
</x-filament-widgets::widget>