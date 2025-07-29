<x-filament::page>
    <x-filament::card class="max-w-screen-lg mx-auto"> 
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            @foreach ($this->getCachedFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </x-filament::card>
</x-filament::page>