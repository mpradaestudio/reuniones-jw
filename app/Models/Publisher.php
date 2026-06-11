<?php

namespace App\Models;

use App\Enums\PublisherPrivilege;
use App\Enums\PublisherStatus;
use App\Models\Concerns\BelongsToCongregation;
use Database\Factories\PublisherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Publicador: miembro de la congregación que participa en el servicio del campo.
 *
 * Decisiones de diseño:
 *  - Sin SoftDeletes: el ciclo de vida se gestiona con `estado`
 *    (activo / irregular / inactivo).
 *  - Sin `fecha_nacimiento` ni `notas` en el MVP.
 *  - Relación con User nullable: un publicador puede existir sin cuenta de sistema.
 *  - Usa BelongsToCongregation para el CongregationScope y la asignación automática
 *    de congregation_id al crear.
 */
class Publisher extends Model
{
    use BelongsToCongregation;

    /** @use HasFactory<PublisherFactory> */
    use HasFactory;

    protected $fillable = [
        'congregation_id',
        'user_id',
        'nombre',
        'apellidos',
        'genero',
        'fecha_bautismo',
        'estado',
        'privilegio',
        'es_nombrado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => PublisherStatus::class,
            'privilegio' => PublisherPrivilege::class,
            'es_nombrado' => 'boolean',
            'fecha_bautismo' => 'date',
        ];
    }

    /**
     * Cuenta de sistema vinculada (opcional).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Nombre completo para la interfaz.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellidos}");
    }

    public function isActive(): bool
    {
        return $this->estado === PublisherStatus::Active;
    }

    public function isElder(): bool
    {
        return $this->privilegio === PublisherPrivilege::Elder;
    }
}
