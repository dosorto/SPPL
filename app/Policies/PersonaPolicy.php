<?php

namespace App\Policies;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PersonaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_personas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Persona $persona): bool
    {
        return $user->can('view_personas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_personas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Persona $persona): bool
    {
        return $user->can('update_personas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Persona $persona): bool
    {
        return $user->can('delete_personas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Persona $persona): bool
    {
        return $user->can('restore_personas');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Persona $persona): bool
    {
        return $user->can('force_delete_personas');
    }
}
