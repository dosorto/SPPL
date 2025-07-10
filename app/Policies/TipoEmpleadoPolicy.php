<?php

namespace App\Policies;

use App\Models\TipoEmpleado;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TipoEmpleadoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_tipo_empleados');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TipoEmpleado $tipoEmpleado): bool
    {
        return $user->can('view_tipo_empleados');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_tipo_empleados');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TipoEmpleado $tipoEmpleado): bool
    {
        return $user->can('update_tipo_empleados');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TipoEmpleado $tipoEmpleado): bool
    {
        return $user->can('delete_tipo_empleados');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TipoEmpleado $tipoEmpleado): bool
    {
        return $user->can('restore_tipo_empleados');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TipoEmpleado $tipoEmpleado): bool
    {
        return $user->can('force_delete_tipo_empleados');
    }
}
