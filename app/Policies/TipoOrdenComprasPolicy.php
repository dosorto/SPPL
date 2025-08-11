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
            return $user->can('view_tipo_orden_compras');
        }

        public function create(User $user): bool
        {
            return $user->can('create_tipo_orden_compras');
        }

        public function update(User $user, TipoOrdenCompras $tipoOrdenCompras): bool
        {
            return $user->can('update_tipo_orden_compras');
        }

        public function delete(User $user, TipoOrdenCompras $tipoOrdenCompras): bool
        {
            return $user->can('delete_tipo_orden_compras');
        }

        public function restore(User $user, TipoOrdenCompras $tipoOrdenCompras): bool
        {
            return $user->can('restore_tipo_orden_compras');
        }

        public function forceDelete(User $user, TipoOrdenCompras $tipoOrdenCompras): bool
        {
            return $user->can('force_delete_tipo_orden_compras');
        }
    

}
