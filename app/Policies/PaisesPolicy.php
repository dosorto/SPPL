<?php

namespace App\Policies;

use App\Models\Paises;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaisesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_paises');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Paises $paises): bool
    {
        return $user->can('view_paises');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_paises');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Paises $paises): bool
    {
        return $user->can('update_paises');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Paises $paises): bool
    {
        return $user->can('delete_paises');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Paises $paises): bool
    {
        return $user->can('restore_paises');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Paises $paises): bool
    {
        return $user->can('force_delete_paises');
    }
}
