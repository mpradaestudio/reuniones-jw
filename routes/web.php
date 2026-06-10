<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CongregationController;
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

    /*
    | Módulo Congregaciones (SuperAdministrador) — CRUD + estado + papelera.
    */
    Route::middleware('permission:congregations.view')->group(function () {
        Route::get('congregaciones', [CongregationController::class, 'index'])->name('congregations.index');
    });

    Route::get('congregaciones/crear', [CongregationController::class, 'create'])
        ->middleware('permission:congregations.create')->name('congregations.create');
    Route::post('congregaciones', [CongregationController::class, 'store'])
        ->middleware('permission:congregations.create')->name('congregations.store');

    Route::get('congregaciones/{congregation}/editar', [CongregationController::class, 'edit'])
        ->middleware('permission:congregations.update')->name('congregations.edit');
    Route::put('congregaciones/{congregation}', [CongregationController::class, 'update'])
        ->middleware('permission:congregations.update')->name('congregations.update');

    Route::patch('congregaciones/{congregation}/estado', [CongregationController::class, 'updateStatus'])
        ->middleware('permission:congregations.toggle-status')->name('congregations.status');

    Route::delete('congregaciones/{congregation}', [CongregationController::class, 'destroy'])
        ->middleware('permission:congregations.delete')->name('congregations.destroy');
    Route::patch('congregaciones/{congregation}/restaurar', [CongregationController::class, 'restore'])
        ->middleware('permission:congregations.delete')->name('congregations.restore')->withTrashed();

    Route::get('usuarios', [PlaceholderController::class, 'users'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('roles', [PlaceholderController::class, 'roles'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('configuracion', [PlaceholderController::class, 'settings'])
        ->name('settings.index');
});
