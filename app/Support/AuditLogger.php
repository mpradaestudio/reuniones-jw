<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Congregation;
use Illuminate\Database\Eloquent\Model;

/**
 * Utilidad para registrar eventos en la tabla audit_logs.
 *
 * El registro automático por módulo se va conectando progresivamente; este
 * módulo (Congregaciones) es el primero en utilizarlo.
 */
class AuditLogger
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public static function record(string $event, ?Model $model = null, array $oldValues = [], array $newValues = []): AuditLog
    {
        $user = auth()->user();

        return AuditLog::create([
            'congregation_id' => self::resolveCongregationId($model, $user),
            'user_id' => $user?->getAuthIdentifier(),
            'event' => $event,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected static function resolveCongregationId(?Model $model, $user): ?int
    {
        if ($model instanceof Congregation) {
            return $model->getKey();
        }

        if ($model !== null && isset($model->congregation_id)) {
            return $model->congregation_id;
        }

        return $user->congregation_id ?? null;
    }
}
