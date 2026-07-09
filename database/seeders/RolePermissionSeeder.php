<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
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
            'users.reset-password',
            // Roles
            'roles.view',
            'roles.assign',
            'roles.manage',
            // Publicadores
            'publishers.view',
            'publishers.create',
            'publishers.update',
            'publishers.toggle-status',
            'publishers.delete',
            // Auditoría (solo lectura)
            'audit.view',
            // Dashboard
            'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // SuperAdministrador: acceso global a todo.
        $superAdmin = Role::findOrCreate('SuperAdministrador', 'web');
        $superAdmin->is_system = true;
        $superAdmin->description = 'Acceso global a toda la plataforma.';
        $superAdmin->save();
        $superAdmin->syncPermissions(Permission::all());

        // AdministradorCongregacion: gestiona su congregación (sin alta/baja global).
        $congregationAdmin = Role::findOrCreate('AdministradorCongregacion', 'web');
        $congregationAdmin->is_system = true;
        $congregationAdmin->description = 'Gestiona los usuarios de su congregación.';
        $congregationAdmin->save();
        $congregationAdmin->syncPermissions([
            'dashboard.view',
            'users.view',
            'users.create',
            'users.update',
            'users.toggle-status',
            'users.reset-password',
            'roles.view',
            'roles.assign',
            'publishers.view',
            'publishers.create',
            'publishers.update',
            'publishers.toggle-status',
            'audit.view',
        ]);

        // Usuario: acceso básico.
        $usuario = Role::findOrCreate('Usuario', 'web');
        $usuario->is_system = true;
        $usuario->description = 'Acceso básico al panel.';
        $usuario->save();
        $usuario->syncPermissions([
            'dashboard.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
