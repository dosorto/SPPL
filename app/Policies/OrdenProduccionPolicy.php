<?php

namespace App\Policies;

use App\Models\OrdenProduccion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrdenProduccionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_ordenes_produccion');
    }

    public function view(User $user, OrdenProduccion $ordenProduccion): bool
    {
        return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('view_ordenes_produccion');
    }

    public function create(User $user): bool
    {
        return $user->can('create_ordenes_produccion');
    }

    public function update(User $user, OrdenProduccion $ordenProduccion): bool
    {
        return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('update_ordenes_produccion');
    }

    public function delete(User $user, OrdenProduccion $ordenProduccion): bool
    {
        return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('delete_ordenes_produccion');
    }

    public function restore(User $user, OrdenProduccion $ordenProduccion): bool
    {
        return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('restore_ordenes_produccion');
    }

    public function forceDelete(User $user, OrdenProduccion $ordenProduccion): bool
    {
        return $user->empresa_id === $ordenProduccion->empresa_id && $user->can('force_delete_ordenes_produccion');
    }
}
