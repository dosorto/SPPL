<?php

namespace App\Policies;

use App\Models\UnidadDeMedidas;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UnidadDeMedidasPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_unidad_de_medidas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UnidadDeMedidas $unidadDeMedidas): bool
    {
        return $user->can('view_unidad_de_medidas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_unidad_de_medidas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UnidadDeMedidas $unidadDeMedidas): bool
    {
        return $user->can('update_unidad_de_medidas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UnidadDeMedidas $unidadDeMedidas): bool
    {
        return $user->can('delete_unidad_de_medidas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UnidadDeMedidas $unidadDeMedidas): bool
    {
        return $user->can('restore_unidad_de_medidas');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UnidadDeMedidas $unidadDeMedidas): bool
    {
        return $user->can('force_delete_unidad_de_medidas');
    }
}
