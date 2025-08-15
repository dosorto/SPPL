@php
    use Illuminate\Support\Facades\Auth;
    use Filament\Facades\Filament;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    $user = Auth::user();
    
    // Mapeo de nombres de recursos a nombres personalizados para mostrar
    $customResourceNames = [
        'detalle-nominas' => 'Historial de Pagos',
        // Agregar aquí más nombres personalizados si se necesitan en el futuro
        // 'resource.name' => 'Nombre personalizado',
    ];
    
    // Obtenemos todas las rutas que comienzan con "filament." para encontrar recursos
    $routes = Route::getRoutes();
    $resourceRoutes = [];
    
    foreach ($routes as $route) {
        $name = $route->getName();
        if ($name && Str::startsWith($name, 'filament.') && Str::contains($name, '.resources.') && Str::endsWith($name, '.index')) {
            $resourceName = Str::of($name)->after('.resources.')->before('.index')->toString();
            $resourceClass = null;
            // Intentar obtener la clase del recurso
            try {
                $resourceClass = "App\\Filament\\Resources\\" . Str::studly($resourceName) . "Resource";
                if (!class_exists($resourceClass)) {
                    $resourceClass = null;
                }
            } catch (\Throwable $e) {
                $resourceClass = null;
            }
            // Si la clase existe y tiene el método getNavigationLabel, usarlo
            if ($resourceClass && method_exists($resourceClass, 'getNavigationLabel')) {
                try {
                    $displayName = $resourceClass::getNavigationLabel();
                } catch (\Throwable $e) {
                    // Si falla, usar nombre personalizado o el generado
                    $displayName = Str::of($resourceName)->replace('.', ' ')->replace('-', ' ')->headline();
                    // Usar nombre personalizado si existe
                    if (array_key_exists($resourceName, $customResourceNames)) {
                        $displayName = $customResourceNames[$resourceName];
                    }
                }
            } else {
                // Si no, usar el nombre generado
                $displayName = Str::of($resourceName)->replace('.', ' ')->headline();
                // Usar nombre personalizado si existe
                if (array_key_exists($resourceName, $customResourceNames)) {
                    $displayName = $customResourceNames[$resourceName];
                }
            }
            // Registramos el nombre real del recurso en la consola para depuración
            // echo "<pre>Recurso: " . $resourceName . " -> " . $displayName . "</pre>";
            
            // Forzar específicamente el nombre para detalle-nominas
            if ($resourceName === 'detalle-nominas') {
                $displayName = 'Historial de Pagos';
            }
            $url = route($name);
            $resourceRoutes[$resourceName] = [
                'name' => $displayName,
                'icon' => '💼', // Icono predeterminado
                'url' => $url,
                'group' => null, // No tenemos información de grupo desde las rutas
                'badge' => null,
                'badgeColor' => null,
            ];
        }
    }
    
    // Verificar permisos del usuario para cada recurso
    $availableResources = collect($resourceRoutes)
        ->filter(function ($resource, $name) use ($user) {
            // Convertir el nombre del recurso a formato de permiso (view_any_resource_name)
            $permissionName = 'view_any_' . Str::of($name)->replace('.', '_')->snake();
            
            // Si el usuario tiene el rol 'root' o tiene el permiso específico
            return $user->hasRole('root') || $user->can($permissionName);
        })
        ->sortBy('name')
        ->values()
        ->toArray();
        
    // Agrupamos por categorías comunes (no tenemos grupos reales)
    $resources = collect($availableResources)->groupBy(function ($resource) {
        if (Str::contains(strtolower($resource['name']), ['usuario', 'role', 'permission'])) {
            return 'Usuarios y Permisos';
        } elseif (Str::contains(strtolower($resource['name']), ['config', 'setting'])) {
            return 'Configuración';
        } elseif (Str::contains(strtolower($resource['name']), ['empresa', 'sucursal'])) {
            return 'Empresas';
        } else {
            return 'General';
        }
    });
@endphp

@if(count($resources) > 0)
<div class="ml-3 relative">
    <div x-data="{ open: false }" @click.away="open = false" class="relative">
        <button 
            @click="open = !open" 
            type="button" 
            class="inline-flex items-center px-3 py-2 border border-amber-500 text-sm leading-4 font-semibold rounded-md text-amber-800 bg-amber-100 hover:bg-amber-200 focus:outline-none transition shadow"
        >
            <span class="mr-2">🏠</span>
            <span>Menú Principal</span>
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
            style="min-width: 250px; top: 100%;"
        >
            <div class="py-1 overflow-y-auto" role="menu" aria-orientation="vertical" style="max-height: 400px;">
                @forelse($resources as $group => $groupedResources)
                    @if($group)
                        <div class="px-4 py-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">
                            {{ $group }}
                        </div>
                    @endif
                    
                    @foreach($groupedResources as $resource)
                        <a 
                            href="{{ $resource['url'] }}" 
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                            role="menuitem"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="mr-2">{{ $resource['icon'] }}</span>
                                    {{ $resource['name'] }}
                                </div>
                                @if($resource['badge'])
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium leading-none rounded-full {{ $resource['badgeColor'] ? 'bg-'.$resource['badgeColor'].'-100 text-'.$resource['badgeColor'].'-800' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $resource['badge'] }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                    
                    @if(!$loop->last)
                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                    @endif
                @empty
                    <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-8 w-8 text-gray-400 dark:text-gray-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>No tienes acceso a recursos</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif
