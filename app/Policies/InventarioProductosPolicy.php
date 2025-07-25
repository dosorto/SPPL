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
        return $user->can('view_any_inventario_productos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InventarioProductos $inventarioProductos): bool
    {
        return $user->can('view_inventario_productos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_inventario_productos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InventarioProductos $inventarioProductos): bool
    {
        return $user->can('update_inventario_productos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InventarioProductos $inventarioProductos): bool
    {
         return $user->can('delete_inventario_productos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InventarioProductos $inventarioProductos): bool
    {
        return $user->can('restore_inventario_productos');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InventarioProductos $inventarioProductos): bool
    {
        return $user->can('force_delete_inventario_productos');
    }

}
