<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

/**
 * Autorización del módulo Auditoría (solo lectura).
 *
 * Defensa en profundidad (igual que Usuarios/Roles):
 *  - El middleware `permission:audit.view` valida el permiso de Spatie en la ruta.
 *  - Esta Policy revalida el permiso y, además, aplica el aislamiento por
 *    congregación en el acceso al detalle.
 *
 * Acceso aprobado:
 *  - SuperAdministrador: ve toda la auditoría (incluidos los eventos globales
 *    sin congregación, `congregation_id = NULL`).
 *  - AdministradorCongregacion (u otros con `audit.view`): solo los registros de
 *    su propia congregación.
 *
 * No hay `before()` con bypass global: el alcance del SuperAdministrador se
 * resuelve explícitamente en cada método para mantener el filtrado por
 * congregación del resto de usuarios. La auditoría es inmutable, por lo que no
 * se definen habilidades de creación/edición/borrado.
 */
class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audit.view');
    }

    public function view(User $user, AuditLog $log): bool
    {
        if (! $user->can('audit.view')) {
            return false;
        }

        // El SuperAdministrador accede a cualquier registro.
        if ($user->isSuperAdmin()) {
            return true;
        }

        // El resto, solo a los registros de su congregación. Los eventos
        // globales (congregation_id = NULL) quedan reservados al SuperAdministrador.
        return $log->congregation_id !== null
            && (int) $log->congregation_id === (int) $user->congregation_id;
    }
}
