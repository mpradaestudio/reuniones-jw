<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaceholderController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

/*
|--------------------------------------------------------------------------
| Invitados (no autenticados)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Autenticados
|--------------------------------------------------------------------------
| Las rutas validan permisos mediante el middleware de Spatie. Los módulos
| de negocio aún no tienen CRUD: se enrutan a páginas temporales.
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('congregaciones', [PlaceholderController::class, 'congregations'])
        ->middleware('permission:congregations.view')
        ->name('congregations.index');

    Route::get('usuarios', [PlaceholderController::class, 'users'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('roles', [PlaceholderController::class, 'roles'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('configuracion', [PlaceholderController::class, 'settings'])
        ->name('settings.index');
});
