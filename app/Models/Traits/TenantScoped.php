<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

trait TenantScoped
{
    /**
     * El método "boot" de un trait se ejecuta automáticamente
     * cuando un modelo que lo usa es inicializado.
     */
    protected static function bootTenantScoped()
    {
        // Ignorar la tabla users para evitar problemas con la autenticación
        if ((new static)->getTable() === 'users') {
            return;
        }
        
        // Verificar si la tabla tiene la columna empresa_id
        if (!Schema::hasColumn((new static)->getTable(), 'empresa_id')) {
            Log::warning('Tabla sin columna empresa_id usando TenantScoped', [
                'modelo' => get_called_class(),
                'tabla' => (new static)->getTable()
            ]);
            return;
        }
        
        // Asegurarnos de que hay un usuario autenticado
        if (!Auth::check()) {
            return;
        }
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Obtener empresa_id usando nuestro helper (si existe la función)
        $empresaId = function_exists('empresa_actual') ? empresa_actual() : session('current_empresa_id');
        
        // Registramos información de depuración
        Log::info('TenantScoped aplicando filtro', [
            'modelo' => get_called_class(),
            'user_id' => $user ? $user->id : null,
            'session_empresa_id' => $empresaId,
            'user_empresa_id' => $user ? $user->empresa_id : null,
            'user_roles' => $user ? $user->roles->pluck('name') : [],
            'session_data' => session()->all()
        ]);
        
        // Si hay una empresa seleccionada en la sesión, aplicar el filtro para todos los usuarios
        if ($empresaId) {
            Log::info('Aplicando filtro por empresa_id de sesión: ' . $empresaId);
            
            static::addGlobalScope('empresa_filtro', function (Builder $builder) use ($empresaId) {
                $builder->where('empresa_id', $empresaId);
            });
            return;
        }
        
        // Si no hay empresa seleccionada pero no es root, aplicar filtro por la empresa del usuario
        if (!$user->hasRole('root')) {
            Log::info('Aplicando filtro por empresa_id del usuario: ' . $user->empresa_id);
            
            static::addGlobalScope('empresa_usuario', function (Builder $builder) use ($user) {
                $builder->where('empresa_id', $user->empresa_id);
            });
            return;
        }
        
        // Para root sin empresa seleccionada, no aplicar filtro (ver todo)
        Log::info('Usuario root sin empresa seleccionada, mostrando todos los registros');
    }
}