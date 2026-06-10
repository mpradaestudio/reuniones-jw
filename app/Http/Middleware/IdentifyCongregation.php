<?php

namespace App\Http\Middleware;

use App\Enums\CongregationStatus;
use App\Models\Congregation;
use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve el tenant (congregación) a partir del subdominio.
 *
 * - Dominio base sin subdominio (p. ej. reuniones-jw.local) => área global
 *   (login del SuperAdministrador). No se fija tenant.
 * - Subdominio (p. ej. central.reuniones-jw.local) => se resuelve la congregación
 *   por su campo `subdominio`. Si no existe o no está activa => 404.
 *
 * El dominio base se configura en config/tenancy.php (APP_DOMAIN).
 */
class IdentifyCongregation
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->resolveSubdomain($request->getHost());

        if ($subdomain !== null) {
            $congregation = Congregation::query()
                ->where('subdominio', $subdomain)
                ->first();

            // No existe, o no está activa => no se permite el acceso al tenant.
            abort_if($congregation === null, 404, 'Congregación no encontrada.');
            abort_if(
                $congregation->estado !== CongregationStatus::Active,
                403,
                'La congregación no está activa.'
            );

            app(Tenant::class)->set($congregation);
            view()->share('currentCongregation', $congregation);
        }

        return $next($request);
    }

    /**
     * Extrae el subdominio comparando el host con el dominio base configurado.
     * Devuelve null si se accede directamente al dominio base.
     */
    protected function resolveSubdomain(string $host): ?string
    {
        // Quitar el puerto si lo hubiera (host:8000).
        $host = explode(':', $host)[0];

        $baseDomain = config('tenancy.base_domain');

        // En desarrollo se puede acceder por IP o localhost sin subdominio.
        if (in_array($host, ['localhost', '127.0.0.1'], true) || ! $baseDomain) {
            return null;
        }

        if ($host === $baseDomain) {
            return null;
        }

        $suffix = '.'.$baseDomain;
        if (str_ends_with($host, $suffix)) {
            $subdomain = substr($host, 0, -strlen($suffix));

            // Subdominios reservados que se tratan como área global.
            if (in_array($subdomain, ['www', 'admin'], true)) {
                return null;
            }

            return $subdomain !== '' ? $subdomain : null;
        }

        return null;
    }
}
