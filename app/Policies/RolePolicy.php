<?php

namespace App\Policies;


use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function before(User $user, $ability): ?bool
    {
        return $user->hasRole('root') ? true : null;
    }

    /** roles del sistema que un usuario normal NO debe tocar ni ver */
    protected function isSystem(Role $role): bool
    {
        // Ajusta esta lista a tus “roles dados en el sistema”
        return in_array($role->name, ['admin'], true);
        // (root ya lo ocultas en la consulta)
    }

    public function viewAny(User $user): bool
    {
        return $user->can('configuraciones_ver');
    }

    public function view(User $user, Role $role): bool
    {
        // Si el usuario TIENE ese rol, puede verlo
        if ($user->roles->contains('id', $role->id)) {
            return true;
        }

        // De lo contrario, no permitir ver roles del sistema
        if ($this->isSystem($role)) {
            return false;
        }

        return $user->can('configuraciones_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('configuraciones_crear');
    }

    public function update(User $user, Role $role): bool
    {
        if ($this->isSystem($role)) return false;          // no editar roles del sistema
        return $user->can('configuraciones_actualizar');
    }

    public function delete(User $user, Role $role): bool
    {
        if ($this->isSystem($role)) return false;          // no eliminar roles del sistema
        return $user->can('configuraciones_eliminar');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('configuraciones_eliminar');
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->can('configuraciones_actualizar');
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    
}
