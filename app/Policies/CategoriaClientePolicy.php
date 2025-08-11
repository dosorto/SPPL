<?php

namespace App\Policies;

use App\Models\CategoriaCliente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoriaClientePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_categoria_cliente');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CategoriaCliente $categoriaCliente): bool
    {
        // Solo puede ver si pertenece a la misma empresa (tenant)
        return $user->empresa_id === $categoriaCliente->empresa_id && $user->can('view_categoria_cliente');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_categoria_cliente');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CategoriaCliente $categoriaCliente): bool
    {
        return $user->empresa_id === $categoriaCliente->empresa_id && $user->can('update_categoria_cliente');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CategoriaCliente $categoriaCliente): bool
    {
        return $user->empresa_id === $categoriaCliente->empresa_id && $user->can('delete_categoria_cliente');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CategoriaCliente $categoriaCliente): bool
    {
        return $user->empresa_id === $categoriaCliente->empresa_id && $user->can('restore_categoria_cliente');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CategoriaCliente $categoriaCliente): bool
    {
        return $user->empresa_id === $categoriaCliente->empresa_id && $user->can('force_delete_categoria_cliente');
    }
}
