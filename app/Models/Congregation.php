<?php

namespace App\Models;

use App\Enums\CongregationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Congregación: entidad raíz del modelo multi-tenant.
 *
 * No se permite borrado físico: usa SoftDeletes para mantener el historial.
 * La baja operativa se realiza con el campo `estado` (inactive / suspended).
 */
class Congregation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'subdominio',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => CongregationStatus::class,
        ];
    }

    /**
     * Usuarios pertenecientes a la congregación.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Registros de auditoría de la congregación.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isActive(): bool
    {
        return $this->estado === CongregationStatus::Active;
    }
}
