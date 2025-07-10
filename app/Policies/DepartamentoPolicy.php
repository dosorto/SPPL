<?php

namespace App\Policies;

use App\Models\Departamento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartamentoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //return $user->hasRole(['admin', 'super_admin']);
        return $user->can('view_any_departamentos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Departamento $departamento): bool
    {
        return $user->can('view_departamentos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_departamentos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Departamento $departamento): bool
    {
        return $user->can('update_departamentos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Departamento $departamento): bool
    {
        return $user->can('delete_departamentos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Departamento $departamento): bool
    {
        return $user->can('restore_departamentos');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Departamento $departamento): bool
    {
        return $user->can('force_delete_departamentos');
    }
}
