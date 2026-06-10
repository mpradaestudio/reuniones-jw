<?php

namespace App\Models\Scopes;

use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Scope que aísla los registros por congregación (multi-tenant).
 *
 * Reglas:
 *  - El SuperAdministrador NO se filtra (ve todas las congregaciones).
 *  - Si hay un tenant resuelto por subdominio, se filtra por ese congregation_id.
 *  - En su defecto, se filtra por la congregación del usuario autenticado.
 *
 * Defensa en profundidad: este scope complementa a las Policies y Form Requests.
 */
class CongregationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        // El SuperAdministrador opera de forma global.
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('SuperAdministrador')) {
            return;
        }

        $column = $model->getTable().'.congregation_id';

        $tenant = app(Tenant::class);
        if ($tenant->hasCongregation()) {
            $builder->where($column, $tenant->id());

            return;
        }

        if ($user && isset($user->congregation_id)) {
            $builder->where($column, $user->congregation_id);

            return;
        }

        // Sin tenant ni usuario: por seguridad no se devuelve nada.
        $builder->whereRaw('1 = 0');
    }
}
