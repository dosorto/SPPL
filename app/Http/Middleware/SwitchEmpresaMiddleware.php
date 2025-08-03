<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SwitchEmpresaMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('switch_empresa')) {
            $empresaId = $request->get('switch_empresa');
            $user = auth()->user();
            
            // Logging para depuración
            Log::info('SwitchEmpresaMiddleware ejecutándose', [
                'user_id' => $user ? $user->id : null,
                'requested_empresa_id' => $empresaId,
                'user_empresa_id' => $user ? $user->empresa_id : null,
                'user_roles' => $user ? $user->roles->pluck('name') : []
            ]);
            
            if ($user) {
                // Caso especial: eliminar filtro de empresa para ver todas (solo root)
                if ($empresaId === 'clear' && $user->hasRole('root')) {
                    // Eliminar la selección de empresa
                    session()->forget('current_empresa_id');
                    $request->session()->forget('current_empresa_id');
                    $request->session()->save();
                    
                    // Log para depuración
                    Log::info('Usuario root eliminó filtro de empresa', [
                        'user_id' => $user->id,
                        'session_id' => $request->session()->getId(),
                        'all_session_data' => $request->session()->all()
                    ]);
                    
                    // Mostrar mensaje de confirmación
                    session()->flash('notification', [
                        'type' => 'info',
                        'message' => "Mostrando datos de todas las empresas"
                    ]);
                    
                    session()->flash('filament.notification', [
                        'status' => 'info',
                        'message' => "Mostrando datos de todas las empresas",
                        'duration' => 3000,
                    ]);
                    
                    // Redireccionar sin el parámetro para limpiar la URL
                    return redirect()->to($request->url());
                }
                // Siempre permitir al usuario root cambiar de empresa
                elseif ($user->hasRole('root')) {
                    // Guardar la empresa seleccionada en la sesión
                    session(['current_empresa_id' => $empresaId]);
                    
                    // Registrar la sesión correctamente
                    $request->session()->put('current_empresa_id', $empresaId);
                    $request->session()->save();
                    
                    // Mostrar mensaje de confirmación
                    $empresa = DB::table('empresas')->where('id', $empresaId)->first();
                    if ($empresa) {
                        // Log de éxito
                        Log::info('Usuario root cambió de empresa', [
                            'user_id' => $user->id,
                            'empresa_id' => $empresaId,
                            'empresa_nombre' => $empresa->nombre,
                            'session_id' => $request->session()->getId(),
                            'all_session_data' => $request->session()->all()
                        ]);
                        
                        session()->flash('notification', [
                            'type' => 'success',
                            'message' => "Empresa cambiada a: {$empresa->nombre}"
                        ]);
                        
                        session()->flash('filament.notification', [
                            'status' => 'success',
                            'message' => "Empresa cambiada a: {$empresa->nombre}",
                            'duration' => 3000,
                        ]);
                    }
                    
                    // Redireccionar sin el parámetro para limpiar la URL
                    return redirect()->to($request->url());
                }
                // Para otros usuarios, verificar acceso
                elseif ($user->canAccessEmpresa($empresaId)) {
                    // Guardar la empresa seleccionada en la sesión
                    session(['current_empresa_id' => $empresaId]);
                    $request->session()->put('current_empresa_id', $empresaId);
                    $request->session()->save();
                    
                    // Mostrar mensaje de confirmación
                    $empresa = DB::table('empresas')->where('id', $empresaId)->first();
                    if ($empresa) {
                        // Log de éxito
                        Log::info('Usuario cambió de empresa', [
                            'user_id' => $user->id,
                            'empresa_id' => $empresaId,
                            'empresa_nombre' => $empresa->nombre,
                            'session_id' => $request->session()->getId(),
                            'all_session_data' => $request->session()->all()
                        ]);
                        
                        session()->flash('notification', [
                            'type' => 'success',
                            'message' => "Empresa cambiada a: {$empresa->nombre}"
                        ]);
                        
                        session()->flash('filament.notification', [
                            'status' => 'success',
                            'message' => "Empresa cambiada a: {$empresa->nombre}",
                            'duration' => 3000,
                        ]);
                    }
                    
                    // Redireccionar sin el parámetro para limpiar la URL
                    return redirect()->to($request->url());
                }
                else {
                    // Log de error
                    Log::warning('Usuario intentó acceder a empresa no autorizada', [
                        'user_id' => $user->id,
                        'empresa_id' => $empresaId
                    ]);
                    
                    session()->flash('filament.notification', [
                        'status' => 'danger',
                        'message' => "No tienes acceso a esa empresa.",
                        'duration' => 3000,
                    ]);
                }
            }
        }

        return $next($request);
    }
}
