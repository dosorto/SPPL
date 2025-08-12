<?php

namespace App\Policies;

use App\Models\InventarioInsumos;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InventarioInsumosPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
       return $user->can('ordenes_producciones_ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InventarioInsumos $inventarioInsumos): bool
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
    public function update(User $user, InventarioInsumos $inventarioInsumos): bool
    {
        return $user->can('ordenes_producciones_editar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InventarioInsumos $inventarioInsumos): bool
    {
        return $user->can('ordenes_producciones_eliminar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InventarioInsumos $inventarioInsumos): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InventarioInsumos $inventarioInsumos): bool
    {
        return false;
    }
}
