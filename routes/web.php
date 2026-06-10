<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\UserController;
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

    Route::get('usuarios', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');

    /*
     | Módulo Usuarios — alta/edición y acciones de escritura.
     | Cada ruta valida el permiso de Spatie y, además, la UserPolicy
     | (misma congregación) a través de los Form Requests / authorize().
     */
    Route::get('usuarios/crear', [UserController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');

    Route::post('usuarios', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');

    Route::get('usuarios/{user}/editar', [UserController::class, 'edit'])
        ->middleware('permission:users.update')
        ->name('users.edit');

    Route::put('usuarios/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('users.update');

    Route::patch('usuarios/{user}/estado', [UserController::class, 'toggleStatus'])
        ->middleware('permission:users.toggle-status')
        ->name('users.toggle-status');

    Route::patch('usuarios/{user}/restablecer-clave', [UserController::class, 'resetPassword'])
        ->middleware('permission:users.reset-password')
        ->name('users.reset-password');

    Route::get('roles', [PlaceholderController::class, 'roles'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('configuracion', [PlaceholderController::class, 'settings'])
        ->name('settings.index');
});
