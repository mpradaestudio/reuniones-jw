<?php

namespace App\Policies;

use App\Models\User;

/**
 * Autorización del módulo Usuarios.
 *
 * Defensa en profundidad (ver docs/ANALISIS.md §4.4):
 *  - El middleware `permission:` valida el permiso de Spatie a nivel de ruta.
 *  - Esta Policy revalida el permiso y, además, exige que el recurso pertenezca
 *    a la MISMA congregación que el usuario que actúa.
 *
 * El SuperAdministrador opera de forma global y omite toda restricción
 * (resuelto en `before()`).
 */
class UserPolicy
{
    /**
     * El SuperAdministrador puede realizar cualquier acción (acceso global).
     * Devolver null deja que se evalúe el método concreto.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view')
            && $this->sameCongregation($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.update')
            && $this->sameCongregation($user, $model);
    }

    /**
     * Activar/desactivar un usuario. Un usuario no puede cambiar su propio estado
     * (evita que un administrador se autobloquee).
     */
    public function toggleStatus(User $user, User $model): bool
    {
        return $user->can('users.toggle-status')
            && $user->id !== $model->id
            && $this->sameCongregation($user, $model);
    }

    /**
     * Restablecer la contraseña de otro usuario. Permiso independiente
     * (`users.reset-password`), no implícito en `users.update`.
     */
    public function resetPassword(User $user, User $model): bool
    {
        return $user->can('users.reset-password')
            && $this->sameCongregation($user, $model);
    }

    /**
     * Dos usuarios pertenecen a la misma congregación.
     *
     * Para usuarios no globales, `congregation_id` nunca es NULL; un usuario sin
     * congregación (NULL) solo podría ser el SuperAdministrador, ya resuelto en
     * `before()`. La comparación estricta evita que dos NULL se consideren iguales.
     */
    protected function sameCongregation(User $user, User $model): bool
    {
        return $user->congregation_id !== null
            && (int) $user->congregation_id === (int) $model->congregation_id;
    }
}
