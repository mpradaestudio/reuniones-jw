<?php

namespace App\Policies;

use App\Models\Congregation;
use App\Models\User;

/**
 * Autorización del módulo de Congregaciones.
 *
 * Las congregaciones son entidades globales: solo las gestiona quien tenga los
 * permisos correspondientes (en la práctica, el SuperAdministrador). El
 * aislamiento por tenant no aplica aquí porque es la entidad raíz.
 */
class CongregationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('congregations.view');
    }

    public function view(User $user, Congregation $congregation): bool
    {
        return $user->can('congregations.view');
    }

    public function create(User $user): bool
    {
        return $user->can('congregations.create');
    }

    public function update(User $user, Congregation $congregation): bool
    {
        return $user->can('congregations.update');
    }

    public function toggleStatus(User $user, Congregation $congregation): bool
    {
        return $user->can('congregations.toggle-status');
    }

    public function delete(User $user, Congregation $congregation): bool
    {
        return $user->can('congregations.delete');
    }

    public function restore(User $user, Congregation $congregation): bool
    {
        return $user->can('congregations.delete');
    }
}
