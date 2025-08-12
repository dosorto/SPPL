<?php

namespace App\Policies;

use App\Models\OrdenProduccion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrdenProduccionPolicy
{
    public function viewAny(User $user): bool
    {
    return $user->can('ordenes_producciones_ver');
    }

    public function view(User $user, OrdenProduccion $ordenProduccion): bool
    {
    return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('ordenes_producciones_ver');
    }

    public function create(User $user): bool
    {
    return $user->can('ordenes_producciones_crear');
    }

    public function update(User $user, OrdenProduccion $ordenProduccion): bool
    {
    return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('ordenes_producciones_actualizar');
    }

    public function delete(User $user, OrdenProduccion $ordenProduccion): bool
    {
    return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('ordenes_producciones_eliminar');
    }

    public function restore(User $user, OrdenProduccion $ordenProduccion): bool
    {
    return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('ordenes_producciones_actualizar');
    }

    public function forceDelete(User $user, OrdenProduccion $ordenProduccion): bool
    {
    return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('ordenes_producciones_eliminar');
    }
}
