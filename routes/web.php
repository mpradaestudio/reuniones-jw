<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\RoleController;
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

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    /*
     | Módulo Roles y Permisos.
     | Lectura: `roles.view`. Gestión (alta/edición/duplicado/eliminación): `roles.manage`.
     | Roles globales; los de sistema están protegidos (RolePolicy + Form Requests).
     | La ruta `roles/crear` se declara antes de `roles/{role}` para evitar colisión.
     */
    Route::get('roles/crear', [RoleController::class, 'create'])
        ->middleware('permission:roles.manage')
        ->name('roles.create');

    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.manage')
        ->name('roles.store');

    Route::get('roles/{role}', [RoleController::class, 'show'])
        ->middleware('permission:roles.view')
        ->name('roles.show');

    Route::get('roles/{role}/editar', [RoleController::class, 'edit'])
        ->middleware('permission:roles.manage')
        ->name('roles.edit');

    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.manage')
        ->name('roles.update');

    Route::get('roles/{role}/duplicar', [RoleController::class, 'duplicateForm'])
        ->middleware('permission:roles.manage')
        ->name('roles.duplicate-form');

    Route::post('roles/{role}/duplicar', [RoleController::class, 'duplicate'])
        ->middleware('permission:roles.manage')
        ->name('roles.duplicate');

    Route::get('roles/{role}/eliminar', [RoleController::class, 'confirmDelete'])
        ->middleware('permission:roles.manage')
        ->name('roles.delete-form');

    Route::delete('roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.manage')
        ->name('roles.destroy');

    Route::get('configuracion', [PlaceholderController::class, 'settings'])
        ->name('settings.index');
});
