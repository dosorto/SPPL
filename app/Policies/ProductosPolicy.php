<?php

namespace App\Policies;

use App\Models\Productos;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductosPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_productos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Productos $productos): bool
    {
        return $user->can('view_productos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_productos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Productos $productos): bool
    {
        return $user->can('update_productos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Productos $productos): bool
    {
        return $user->can('delete_productos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Productos $productos): bool
    {
        return $user->can('restore_productos');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Productos $productos): bool
    {
        return $user->can('force_delete_productos');
    }
}
