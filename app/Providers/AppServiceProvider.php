<?php

namespace App\Providers;

use App\Models\User;
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

        // Paginación con marcado de Bootstrap 5 (stack de UI aprobado).
        Paginator::useBootstrapFive();
    }
}
