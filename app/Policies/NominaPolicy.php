<?php

namespace App\Policies;

use App\Models\Nomina;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NominaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_nominas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Nomina $nomina): bool
    {
        return $user->can('view_nominas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_nominas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Nomina $nomina): bool
    {
        return $user->can('update_nominas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Nomina $nomina): bool
    {
        return $user->can('delete_nominas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Nomina $nomina): bool
    {
        return $user->can('restore_nominas');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Nomina $nomina): bool
    {
        return $user->can('force_delete_nominas');
    }
}
