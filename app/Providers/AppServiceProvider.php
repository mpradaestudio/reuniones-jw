<?php

namespace App\Providers;

use App\Support\Tenant;
use Illuminate\Pagination\Paginator;
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
        // La paginación usa el maquetado de Bootstrap 5 (framework de UI oficial).
        Paginator::useBootstrapFive();
    }
}
