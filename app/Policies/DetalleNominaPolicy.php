<?php

namespace App\Policies;

use App\Models\DetalleNomina;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DetalleNominaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_detalle_nominas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DetalleNomina $detalleNomina): bool
    {
        return $user->can('view_detalle_nominas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_detalle_nominas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DetalleNomina $detalleNomina): bool
    {
        return $user->can('update_detalle_nominas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DetalleNomina $detalleNomina): bool
    {
        return $user->can('delete_detalle_nominas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DetalleNomina $detalleNomina): bool
    {
        return $user->can('restore_detalle_nominas');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DetalleNomina $detalleNomina): bool
    {
        return $user->can('force_delete_detalle_nominas');
    }
}
