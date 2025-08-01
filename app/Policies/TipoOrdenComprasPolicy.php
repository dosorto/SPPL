<?php

namespace App\Policies;

use App\Models\TipoOrdenCompras;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TipoOrdenComprasPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_tipo_orden_compras');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TipoOrdenCompras $tipoOrdenCompras): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TipoOrdenCompras $tipoOrdenCompras): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TipoOrdenCompras $tipoOrdenCompras): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TipoOrdenCompras $tipoOrdenCompras): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TipoOrdenCompras $tipoOrdenCompras): bool
    {
        return false;
    }
}
