<?php

namespace App\Policies;

use App\Models\Rendimiento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RendimientoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('rendimientos_ver');
    }

    public function view(User $user, Rendimiento $rendimiento): bool
    {
        return $user->empresa_id === $rendimiento->empresa_id && $user->can('rendimientos_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('rendimientos_crear');
    }

    public function update(User $user, Rendimiento $rendimiento): bool
    {
        return $user->empresa_id === $rendimiento->empresa_id && $user->can('rendimientos_actualizar');
    }

    public function delete(User $user, Rendimiento $rendimiento): bool
    {
        return $user->empresa_id === $rendimiento->empresa_id && $user->can('rendimientos_eliminar');
    }

    public function restore(User $user, Rendimiento $rendimiento): bool
    {
        return $user->empresa_id === $rendimiento->empresa_id && $user->can('rendimientos_actualizar');
    }

    public function forceDelete(User $user, Rendimiento $rendimiento): bool
    {
        return $user->empresa_id === $rendimiento->empresa_id && $user->can('rendimientos_eliminar');
    }
}
