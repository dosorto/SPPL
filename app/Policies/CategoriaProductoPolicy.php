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
        if ($user->hasRole('root')) {
            return true; // El usuario root puede ver todas las categorías
        }
        return $user->hasPermissionTo('view_any_categoria::producto') && $user->empresa_id !== null;
    }

    public function view(User $user, CategoriaProducto $categoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('view_categoria::producto') && $categoriaProducto->empresa_id === $user->empresa_id;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('create_categoria::producto') && $user->empresa_id !== null;
    }

    public function update(User $user, CategoriaProducto $categoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('update_categoria::producto') && $categoriaProducto->empresa_id === $user->empresa_id;
    }

    public function delete(User $user, CategoriaProducto $categoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('delete_categoria::producto') && $categoriaProducto->empresa_id === $user->empresa_id;
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