<?php

namespace App\Policies;

use App\Models\DepartamentoEmpleado;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartamentoEmpleadoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('recursos_humanos_ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DepartamentoEmpleado $departamentoEmpleado): bool
    {
        return $user->can('recursos_humanos_ver');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('recursos_humanos_crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DepartamentoEmpleado $departamentoEmpleado): bool
    {
        return $user->can('recursos_humanos_actualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DepartamentoEmpleado $departamentoEmpleado): bool
    {
        return $user->can('recursos_humanos_eliminar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DepartamentoEmpleado $departamentoEmpleado): bool
    {
        return $user->can('recursos_humanos_restaurar');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DepartamentoEmpleado $departamentoEmpleado): bool
    {
        return $user->can('force_delete_recursos_humanos');
    }
}
