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
        return $user->can('view_any_categoria_unidades');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->can('view_categoria_unidades');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_categoria_unidades');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->can('update_categoria_unidades');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->can('delete_categoria_unidades');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->can('restore_categoria_unidades');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CategoriaUnidades $categoriaUnidades): bool
    {
        return $user->can('force_delete_categoria_unidades');
    }
}
