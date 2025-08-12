<?php

namespace App\Policies;

use App\Models\InventarioProductos;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InventarioProductosPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('inventario_ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InventarioProductos $inventarioProductos): bool
    {
        return $user->can('inventario_ver');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('inventario_crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InventarioProductos $inventarioProductos): bool
    {
        return $user->can('inventario_actualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InventarioProductos $inventarioProductos): bool
    {
         return $user->can('inventario_eliminar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InventarioProductos $inventarioProductos): bool
    {
        return $user->can('inventario_actualizar');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InventarioProductos $inventarioProductos): bool
    {
        return $user->can('inventario_eliminar');
    }

}
