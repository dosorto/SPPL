<?php

namespace App\Policies;

use App\Models\CajaApertura;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CajaAperturaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ventas_ver');
    }

    public function view(User $user, CajaApertura $cajaApertura): bool
    {
        return $user->can('ventas_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('ventas_crear');
    }

    public function update(User $user, CajaApertura $cajaApertura): bool
    {
        return $user->can('ventas_actualizar');
    }

    public function delete(User $user, CajaApertura $cajaApertura): bool
    {
        return $user->can('ventas_eliminar');
    }

    public function restore(User $user, CajaApertura $cajaApertura): bool
    {
        return $user->can('ventas_actualizar');
    }

    public function forceDelete(User $user, CajaApertura $cajaApertura): bool
    {
        return $user->can('ventas_eliminar');
    }
}

