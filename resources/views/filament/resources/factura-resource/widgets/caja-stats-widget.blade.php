<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Botón Cerrar Caja en la parte superior, fuera del contenido principal --}}
        <div class="flex justify-end mb-4">
            {{ $this->getCerrarCajaAction() }}
        </div>
        
        {{-- Esta parte se encarga de mostrar las 3 tarjetas de estadísticas --}}
        <div class="fi-wi-stats-overview grid gap-6 md:grid-cols-3">
            @foreach ($this->getStats() as $stat)
                {{ $stat }}
            @endforeach
        </div>
        
        {{-- Botón Generar Factura/Aperturar posicionado abajo a la derecha --}}
        <div class="mt-6 flex justify-end">
            {{ $this->getGenerarFacturaAction() }}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>