<?php

namespace App\Policies;

use App\Models\Percepciones;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PercepcionesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_percepciones');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Percepciones $percepciones): bool
    {
        return $user->can('view_percepciones');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_percepciones');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Percepciones $percepciones): bool
    {
        return $user->can('update_percepciones');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Percepciones $percepciones): bool
    {
        return $user->can('delete_percepciones');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Percepciones $percepciones): bool
    {
        return $user->can('restore_percepciones');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Percepciones $percepciones): bool
    {
        return $user->can('force_delete_percepciones');
    }
}
