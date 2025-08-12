<?php

namespace App\Policies;

use App\Models\OrdenCompras;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrdenComprasPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('compras_ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrdenCompras $ordenCompras): bool
    {
        return $user->can('compras_ver') && $ordenCompras->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('compras_crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrdenCompras $ordenCompras): bool
    {
        return $user->can('compras_actualizar') && $ordenCompras->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrdenCompras $ordenCompras): bool
    {
        return $user->can('compras_eliminar') && $ordenCompras->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrdenCompras $ordenCompras): bool
    {
        return $user->can('restore_orden_compras');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrdenCompras $ordenCompras): bool
    {
        return $user->can('force_delete_orden_compras');
    }
}
