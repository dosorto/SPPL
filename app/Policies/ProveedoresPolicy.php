<?php

namespace App\Policies;

use App\Models\Proveedores;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProveedoresPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_proveedores');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Proveedores $proveedores): bool
    {
        return $user->can('view_proveedores');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_proveedores');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Proveedores $proveedores): bool
    {
        return $user->can('update_proveedores');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Proveedores $proveedores): bool
    {
        return $user->can('delete_proveedores');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Proveedores $proveedores): bool
    {
        return $user->can('restore_proveedores');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Proveedores $proveedores): bool
    {
        return $user->can('force_delete_proveedores');
    }
}
