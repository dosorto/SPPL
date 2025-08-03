@php
    use App\Models\Empresa;
    $user = auth()->user();
    $currentEmpresaId = session('current_empresa_id', $user?->empresa_id);
    $currentEmpresa = $currentEmpresaId ? Empresa::find($currentEmpresaId) : null;
@endphp

<x-filament-panels::page>
    <div class="mb-4">
        @if($currentEmpresa)
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-300">Empresa activa:</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-xs font-semibold">
                    {{ $currentEmpresa->nombre }}
                </span>
            </div>
            @if(session('filament.notification'))
                <div class="mt-2">
                    <x-filament::notification
                        :status="session('filament.notification.status')"
                        :message="session('filament.notification.message')"
                        :duration="session('filament.notification.duration') ?? 3000"
                    />
                </div>
            @endif
        @endif
    </div>
    {{-- ...el resto del contenido de la página... --}}
</x-filament-panels::page>
