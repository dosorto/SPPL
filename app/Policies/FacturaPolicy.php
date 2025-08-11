<?php

namespace App\Policies;

use App\Models\Factura;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FacturaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ventas_ver');
    }

    public function view(User $user, Factura $factura): bool
    {
        return $user->can('ventas_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('ventas_crear');
    }

    public function update(User $user, Factura $factura): bool
    {
        return $user->can('ventas_actualizar');
    }

    public function delete(User $user, Factura $factura): bool
    {
        return $user->can('ventas_eliminar');
    }

    public function restore(User $user, Factura $factura): bool
    {
        return $user->can('ventas_actualizar');
    }

    public function forceDelete(User $user, Factura $factura): bool
    {
        return $user->can('ventas_eliminar');
    }
}
