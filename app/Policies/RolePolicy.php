<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * Autorización del módulo Roles y Permisos.
 *
 * Los roles son GLOBALes y solo los gestiona quien tenga el permiso
 * `roles.manage` (en la práctica, el SuperAdministrador). La visualización
 * requiere `roles.view`.
 *
 * No se define `before()` con bypass global: así, las protecciones de los roles
 * de sistema se aplican a TODOS, incluido el SuperAdministrador (p. ej. no se
 * puede eliminar un rol de sistema).
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.manage');
    }

    /**
     * Editar un rol (permisos y, salvo roles de sistema, su nombre).
     * La inmutabilidad del nombre de los roles de sistema se valida en el
     * Form Request correspondiente.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.manage');
    }

    /**
     * Eliminar un rol: solo roles personalizados (los de sistema están protegidos).
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->can('roles.manage') && ! $role->isSystem();
    }

    /**
     * Duplicar un rol (clonar sus permisos en un rol nuevo).
     */
    public function duplicate(User $user, Role $role): bool
    {
        return $user->can('roles.manage');
    }
}
