<?php

namespace App\Policies;

use App\Models\EmpleadoDeducciones;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmpleadoDeduccionesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_empleado_deducciones');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmpleadoDeducciones $empleadoDeducciones): bool
    {
        return $user->can('view_empleado_deducciones');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_empleado_deducciones');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmpleadoDeducciones $empleadoDeducciones): bool
    {
        return $user->can('update_empleado_deducciones');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmpleadoDeducciones $empleadoDeducciones): bool
    {
        return $user->can('delete_empleado_deducciones');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmpleadoDeducciones $empleadoDeducciones): bool
    {
        return $user->can('restore_empleado_deducciones');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmpleadoDeducciones $empleadoDeducciones): bool
    {
        return $user->can('force_delete_empleado_deducciones');
    }
}
