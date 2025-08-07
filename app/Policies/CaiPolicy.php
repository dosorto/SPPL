<?php

namespace App\Policies;

use App\Models\Cai;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CaiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_cais');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cai $cai): bool
    {
        return $user->can('view_cais');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_cais');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cai $cai): bool
    {
        return $user->can('update_cais');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cai $cai): bool
    {
        return $user->can('delete_cais');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cai $cai): bool
    {
        return $user->can('restore_cais');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cai $cai): bool
    {
        return $user->can('force_delete_cais');
    }
}
