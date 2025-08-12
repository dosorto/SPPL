<?php

namespace App\Policies;

use App\Models\OrdenComprasInsumos;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrdenComprasInsumosPolicy
{
    /**
     * Determine whether the user can view any models .
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ordenes_producciones_ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrdenComprasInsumos $ordenComprasInsumos): bool
    {
        return $user->can('ordenes_producciones_ver');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('ordenes_producciones_crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrdenComprasInsumos $ordenComprasInsumos): bool
    {
        return $user->can('ordenes_producciones_actualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrdenComprasInsumos $ordenComprasInsumos): bool
    {
        return $user->can('ordenes_producciones_eliminar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrdenComprasInsumos $ordenComprasInsumos): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrdenComprasInsumos $ordenComprasInsumos): bool
    {
        return false;
    }
}
