@php
    use App\Models\Empresa;
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    
    // Solo mostrar el selector si el usuario tiene el rol root
    $showSelector = $user && $user->hasRole('root');
    
    if ($showSelector) {
        // Obtener las empresas accesibles
        $empresas = $user->getAccessibleEmpresas();
        
        // Obtener la empresa actual
        $currentEmpresaId = session('current_empresa_id', $user->empresa_id);
        $currentEmpresa = Empresa::find($currentEmpresaId);
    }
@endphp


@if($showSelector && isset($empresas) && $empresas->count() > 0)
<div class="ml-3 relative">
    <div x-data="{ open: false }" @click.away="open = false" class="relative">
        <button 
            @click="open = !open" 
            type="button" 
            class="inline-flex items-center px-3 py-2 border border-amber-500 text-sm leading-4 font-semibold rounded-md text-amber-800 bg-amber-100 hover:bg-amber-200 focus:outline-none transition shadow"
        >
            <span class="mr-2">🏢</span>
            @if(session('current_empresa_id') && $currentEmpresa)
                <span class="truncate max-w-[120px]">{{ $currentEmpresa->nombre }}</span>
            @else
                <span>Todas las Empresas</span>
            @endif
            <svg class="ml-2 -mr-0.5 h-4 w-4 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.293l3.71-4.06a.75.75 0 111.08 1.04l-4.25 4.65a.75.75 0 01-1.08 0l-4.25-4.65a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="open" 
            x-transition:enter="transition ease-out duration-100" 
            x-transition:enter-start="transform opacity-0 scale-95" 
            x-transition:enter-end="transform opacity-100 scale-100" 
            x-transition:leave="transition ease-in duration-75" 
            x-transition:leave-start="transform opacity-100 scale-100" 
            x-transition:leave-end="transform opacity-0 scale-95" 
            class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50"
            style="min-width: 200px; top: 100%;"
        >
            <div class="py-1" role="menu" aria-orientation="vertical">
                <!-- Opción para ver todas las empresas (elimina filtro) -->
                <a 
                    href="{{ request()->fullUrlWithQuery(['switch_empresa' => 'clear']) }}" 
                    class="block px-4 py-2 text-sm {{ !session('current_empresa_id') ? 'bg-amber-100 text-amber-800 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                    role="menuitem"
                >
                    <div class="flex items-center">
                        <span class="mr-2">🌐</span>
                        <span>Ver Todas las Empresas</span>
                    </div>
                </a>
                
                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                
                @foreach($empresas as $empresa)
                    <a 
                        href="{{ request()->fullUrlWithQuery(['switch_empresa' => $empresa->id]) }}" 
                        class="block px-4 py-2 text-sm {{ (session('current_empresa_id') && session('current_empresa_id') == $empresa->id) ? 'bg-amber-100 text-amber-800 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                        role="menuitem"
                    >
                        <div class="flex items-center">
                            @if(session('current_empresa_id') && session('current_empresa_id') == $empresa->id)
                                <span class="mr-2">✓</span>
                            @else
                                <span class="mr-2">🏢</span>
                            @endif
                            {{ $empresa->nombre }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
