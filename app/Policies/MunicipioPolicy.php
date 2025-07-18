<?php

namespace App\Policies;

use App\Models\Municipio;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MunicipioPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_municipios');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Municipio $municipio): bool
    {
        return $user->can('view_municipios');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_municipios');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Municipio $municipio): bool
    {
        return $user->can('update_municipios');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Municipio $municipio): bool
    {
        return $user->can('delete_municipios');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Municipio $municipio): bool
    {
        return $user->can('restore_municipios');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Municipio $municipio): bool
    {
        return $user->can('force_delete_municipios');
    }
}
