<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro de eventos de auditoría en `audit_logs`.
 *
 * Escritura explícita por módulo (según lo previsto en el modelo AuditLog).
 * Captura al autor de la acción (usuario autenticado), la congregación afectada
 * y el contexto de la petición (IP y user agent).
 */
class AuditLogger
{
    /**
     * Registra un evento de auditoría.
     *
     * @param  string  $event  Identificador del evento (p. ej. `user.created`).
     * @param  Model|null  $auditable  Modelo afectado (relación polimórfica).
     * @param  array<string, mixed>  $oldValues  Valores previos (solo cambios).
     * @param  array<string, mixed>  $newValues  Valores nuevos (solo cambios).
     */
    public static function record(
        string $event,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
    ): AuditLog {
        $actor = auth()->user();
        $request = request();

        // Congregación afectada: la del modelo auditado si la tiene; si no, la
        // del autor de la acción (NULL para acciones globales del SuperAdmin).
        $congregationId = null;
        if ($auditable !== null && isset($auditable->congregation_id)) {
            $congregationId = $auditable->congregation_id;
        } elseif ($actor !== null) {
            $congregationId = $actor->congregation_id;
        }

        return AuditLog::create([
            'congregation_id' => $congregationId,
            'user_id' => $actor?->getKey(),
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues === [] ? null : $oldValues,
            'new_values' => $newValues === [] ? null : $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? $request->userAgent() : null,
        ]);
    }
}
