<?php

namespace App\Policies;

use App\Models\CategoriaUnidades;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoriaUnidadesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['root']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->hasRole(['root']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['root']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->hasRole(['root']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->hasRole(['root']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->hasRole(['root']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->hasRole(['root']);
    }
}
