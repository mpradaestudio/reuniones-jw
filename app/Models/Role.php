<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Rol de la aplicación (extiende el modelo de spatie/laravel-permission).
 *
 * Los roles son GLOBALes (sin Teams). Los roles de sistema están protegidos:
 * no pueden renombrarse ni eliminarse (ver UserPolicy/RolePolicy y Form Requests).
 */
class Role extends SpatieRole
{
    /**
     * Roles protegidos del sistema.
     */
    public const SYSTEM_ROLES = [
        'SuperAdministrador',
        'AdministradorCongregacion',
        'Usuario',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'is_system' => 'boolean',
        ]);
    }

    /**
     * ¿Es un rol de sistema protegido?
     */
    public function isSystem(): bool
    {
        return (bool) $this->is_system || in_array($this->name, self::SYSTEM_ROLES, true);
    }
}
