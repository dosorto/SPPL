<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\CajaApertura; // 👈 ¡Importante! Asegúrate de importar tu modelo.
use Symfony\Component\HttpFoundation\Response;

class CheckCajaAbierta
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       
        if (Auth::check() && !Session::has('apertura_id')) {

            
            $aperturaActiva = CajaApertura::where('user_id', Auth::id()) 
                                         ->whereNull('fecha_cierre')      
                                         ->latest('fecha_apertura')       
                                         ->first();                       

            
            if ($aperturaActiva) {
                Session::put('apertura_id', $aperturaActiva->id);
            }
        }

        
        return $next($request);
    }
}