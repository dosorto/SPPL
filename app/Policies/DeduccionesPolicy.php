<?php

namespace App\Policies;

use App\Models\Deducciones;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DeduccionesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('nominas_ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Deducciones $deducciones): bool
    {
        return $user->can('nominas_ver');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('nominas_crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Deducciones $deducciones): bool
    {
        return $user->can('nominas_actualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Deducciones $deducciones): bool
    {
        return $user->can('nominas_eliminar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Deducciones $deducciones): bool
    {
        return $user->can('nominas_actualizar');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Deducciones $deducciones): bool
    {
        return $user->can('nominas_eliminar');
    }
}
