<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Confirmar Recepción y Actualizar Inventario
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>