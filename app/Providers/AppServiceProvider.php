<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Publisher;
use App\Models\Role;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\PublisherPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Support\Tenant;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tenant activo (congregación resuelta por subdominio) durante la petición.
        $this->app->singleton(Tenant::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Autorización del módulo Usuarios (permiso Spatie + misma congregación).
        Gate::policy(User::class, UserPolicy::class);

        // Autorización del módulo Roles y Permisos.
        Gate::policy(Role::class, RolePolicy::class);

        // Autorización del módulo Auditoría (solo lectura + aislamiento por congregación).
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        // Autorización del módulo Publicadores.
        Gate::policy(Publisher::class, PublisherPolicy::class);

        // Paginación con marcado de Bootstrap 5 (stack de UI aprobado).
        Paginator::useBootstrapFive();
    }
}
