<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea los permisos y roles iniciales (RBAC con spatie/laravel-permission).
 *
 * Roles: SuperAdministrador, AdministradorCongregacion, Usuario.
 * El catálogo es extensible: añadir nuevos permisos/roles aquí no requiere
 * cambios de arquitectura.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar la caché de permisos antes de sembrar.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Congregaciones
            'congregations.view',
            'congregations.create',
            'congregations.update',
            'congregations.toggle-status',
            // Usuarios
            'users.view',
            'users.create',
            'users.update',
            'users.toggle-status',
            // Roles
            'roles.view',
            'roles.assign',
            'roles.manage',
            // Dashboard
            'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // SuperAdministrador: acceso global a todo.
        $superAdmin = Role::findOrCreate('SuperAdministrador', 'web');
        $superAdmin->syncPermissions(Permission::all());

        // AdministradorCongregacion: gestiona su congregación (sin alta/baja global).
        $congregationAdmin = Role::findOrCreate('AdministradorCongregacion', 'web');
        $congregationAdmin->syncPermissions([
            'dashboard.view',
            'users.view',
            'users.create',
            'users.update',
            'users.toggle-status',
            'roles.view',
            'roles.assign',
        ]);

        // Usuario: acceso básico.
        $usuario = Role::findOrCreate('Usuario', 'web');
        $usuario->syncPermissions([
            'dashboard.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
