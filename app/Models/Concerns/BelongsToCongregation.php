<?php

namespace App\Models\Concerns;

use App\Models\Congregation;
use App\Models\Scopes\CongregationScope;
use App\Support\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait para modelos de negocio que pertenecen a una congregación.
 *
 * Aplica automáticamente:
 *  - El Global Scope CongregationScope (filtra toda consulta por congregation_id).
 *  - La asignación automática de congregation_id al crear, según el tenant activo
 *    o la congregación del usuario autenticado.
 *
 * Pensado para los futuros modelos de negocio (horarios, asignaciones, discursos...).
 */
trait BelongsToCongregation
{
    public static function bootBelongsToCongregation(): void
    {
        static::addGlobalScope(new CongregationScope());

        static::creating(function ($model) {
            if (! empty($model->congregation_id)) {
                return;
            }

            $tenant = app(Tenant::class);
            if ($tenant->hasCongregation()) {
                $model->congregation_id = $tenant->id();

                return;
            }

            $user = auth()->user();
            if ($user && isset($user->congregation_id)) {
                $model->congregation_id = $user->congregation_id;
            }
        });
    }

    public function congregation(): BelongsTo
    {
        return $this->belongsTo(Congregation::class);
    }
}
