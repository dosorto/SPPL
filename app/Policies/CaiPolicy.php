<?php

namespace App\Policies;

use App\Models\Cai;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CaiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ventas_ver');
    }

    public function view(User $user, Cai $cai): bool
    {
        return $user->can('ventas_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('ventas_crear');
    }

    public function update(User $user, Cai $cai): bool
    {
        return $user->can('ventas_actualizar');
    }

    public function delete(User $user, Cai $cai): bool
    {
        return $user->can('ventas_eliminar');
    }

    public function restore(User $user, Cai $cai): bool
    {
        return $user->can('ventas_actualizar');
    }

    public function forceDelete(User $user, Cai $cai): bool
    {
        return $user->can('ventas_eliminar');
    }
}
