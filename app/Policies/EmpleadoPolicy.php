<?php

namespace App\Policies;

use App\Models\Empleado;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmpleadoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_empleados');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Empleado $empleado): bool
    {
        return $user->can('view_empleados');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_empleados');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Empleado $empleado): bool
    {
        return $user->can('update_empleados');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Empleado $empleado): bool
    {
        return $user->can('delete_empleados');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Empleado $empleado): bool
    {
        return $user->can('restore_empleados');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Empleado $empleado): bool
    {
        return $user->can('force_delete_empleados');
    }
}
