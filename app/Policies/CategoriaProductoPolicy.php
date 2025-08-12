<?php

namespace App\Policies;

use App\Models\CategoriaProducto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoriaProductoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('inventario_ver');
    }

    public function view(User $user, CategoriaProducto $categoriaProducto): bool
    {
        return $user->can('inventario_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('inventario_crear');
    }

    public function update(User $user, CategoriaProducto $categoriaProducto): bool
    {
        return $user->can('inventario_actualizar');
    }

    public function delete(User $user, CategoriaProducto $categoriaProducto): bool
    {
        return $user->can('inventario_eliminar');
    }

    public function restore(User $user, CategoriaProducto $categoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('restore_categoria::producto') && $categoriaProducto->empresa_id === $user->empresa_id;
    }

    public function forceDelete(User $user, CategoriaProducto $categoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('force_delete_categoria::producto') && $categoriaProducto->empresa_id === $user->empresa_id;
    }
}