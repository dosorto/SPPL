<?php

namespace App\Policies;

use App\Models\SubcategoriaProducto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubcategoriaProductoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->hasRole('root')) {
            return true; // El usuario root puede ver todas las subcategorías
        }
        return $user->hasPermissionTo('view_any_subcategoria_productos') && $user->empresa_id !== null;
    }

    public function view(User $user, SubcategoriaProducto $subcategoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('view_subcategoria_productos') && $subcategoriaProducto->empresa_id === $user->empresa_id;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('create_subcategoria_productos') && $user->empresa_id !== null;
    }

    public function update(User $user, SubcategoriaProducto $subcategoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('update_subcategoria_productos') && $subcategoriaProducto->empresa_id === $user->empresa_id;
    }

    public function delete(User $user, SubcategoriaProducto $subcategoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('delete_subcategoria_productos') && $subcategoriaProducto->empresa_id === $user->empresa_id;
    }

    public function restore(User $user, SubcategoriaProducto $subcategoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('restore_subcategoria_productos') && $subcategoriaProducto->empresa_id === $user->empresa_id;
    }

    public function forceDelete(User $user, SubcategoriaProducto $subcategoriaProducto): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }
        return $user->hasPermissionTo('force_delete_subcategoria_productos') && $subcategoriaProducto->empresa_id === $user->empresa_id;
    }
}