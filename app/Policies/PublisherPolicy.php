<?php

namespace App\Policies;

use App\Enums\PublisherPrivilege;
use App\Enums\PublisherStatus;
use App\Models\Publisher;
use App\Models\User;

/**
 * Autorización del módulo Publicadores.
 *
 * Defensa en profundidad (misma estrategia que UserPolicy y RolePolicy):
 *  - El middleware `permission:publishers.*` valida el permiso de Spatie en la ruta.
 *  - Esta Policy revalida el permiso y exige aislamiento por congregación.
 *
 * Decisiones aprobadas:
 *  - SuperAdministrador: acceso global vía before() (excepto toggleStatus, que
 *    aplica siempre su propio método para la protección del último anciano).
 *  - publishers.delete: SOLO SuperAdministrador (decisión C).
 *  - Protección del último anciano activo: impide toggle-status si dejaría a la
 *    congregación sin ningún anciano activo (decisión D).
 */
class PublisherPolicy
{
    /**
     * El SuperAdministrador puede realizar cualquier acción (acceso global).
     * Devuelve null para que se evalúe el método concreto cuando sea necesario
     * aplicar reglas de negocio adicionales (toggleStatus → protección del anciano).
     */
    public function before(User $user, string $ability): ?bool
    {
        // toggleStatus siempre se evalúa en su propio método (protección del
        // último anciano, análoga a la del último AdministradorCongregacion).
        if ($ability === 'toggleStatus') {
            return null;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('publishers.view');
    }

    public function view(User $user, Publisher $publisher): bool
    {
        return $user->can('publishers.view')
            && $this->sameCongregation($user, $publisher);
    }

    public function create(User $user): bool
    {
        return $user->can('publishers.create');
    }

    public function update(User $user, Publisher $publisher): bool
    {
        return $user->can('publishers.update')
            && $this->sameCongregation($user, $publisher);
    }

    /**
     * Cambiar el estado de un publicador (activo / irregular / inactivo).
     *
     * Regla D — protección del último anciano activo:
     *  Si el cambio dejaría a la congregación sin ningún anciano activo, se deniega.
     *  Esta lógica se delega al controlador (que lanza ValidationException), pero
     *  la Policy también debe autorizar la acción base.
     */
    public function toggleStatus(User $user, Publisher $publisher): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->can('publishers.toggle-status')
            && $this->sameCongregation($user, $publisher);
    }

    /**
     * Eliminar un publicador — solo SuperAdministrador (decisión C).
     * El before() ya devuelve true para SuperAdmin antes de llegar aquí, pero se
     * mantiene el método explícito como documentación y defensa en profundidad.
     */
    public function delete(User $user, Publisher $publisher): bool
    {
        // before() ya concede acceso al SuperAdmin; este método solo alcanza a
        // roles que no son SuperAdmin.
        return false;
    }

    /** Comprueba que el actor y el publicador pertenecen a la misma congregación. */
    protected function sameCongregation(User $user, Publisher $publisher): bool
    {
        return $user->congregation_id !== null
            && (int) $user->congregation_id === (int) $publisher->congregation_id;
    }
}
