<?php

namespace App\Policies;

use App\Models\DetalleNominas;
use App\Models\User;

class DetalleNominasPolicy
{
    /**
     * Permitir ver cualquier DetalleNominas solo si pertenece a la empresa del usuario.
     */
    public function view(User $user, DetalleNominas $detalleNomina): bool
    {
        return $user->empresa_id === $detalleNomina->empresa_id;
    }

    /**
     * Permitir actualizar solo si pertenece a la empresa del usuario.
     */
    public function update(User $user, DetalleNominas $detalleNomina): bool
    {
        return $user->empresa_id === $detalleNomina->empresa_id;
    }

    /**
     * Permitir eliminar solo si pertenece a la empresa del usuario.
     */
    public function delete(User $user, DetalleNominas $detalleNomina): bool
    {
        return $user->empresa_id === $detalleNomina->empresa_id;
    }

    /**
     * Permitir ver la lista solo si tiene permiso general (puedes personalizar esto).
     */
    public function viewAny(User $user): bool
    {
        return true; // O puedes usar $user->can('view_any_detalle_nominas')
    }
}
