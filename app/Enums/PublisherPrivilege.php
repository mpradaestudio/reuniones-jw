<?php

namespace App\Enums;

/**
 * Privilegio espiritual de un publicador.
 * Anciano y siervo ministerial solo pueden asignarse a hombres.
 */
enum PublisherPrivilege: string
{
    case Publisher = 'publicador';
    case MinisterialServant = 'siervo_ministerial';
    case Elder = 'anciano';

    public function label(): string
    {
        return match ($this) {
            self::Publisher => 'Publicador',
            self::MinisterialServant => 'Siervo ministerial',
            self::Elder => 'Anciano',
        };
    }

    /** Clase de badge Bootstrap 5 para la UI. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Publisher => 'text-bg-secondary',
            self::MinisterialServant => 'text-bg-info',
            self::Elder => 'text-bg-primary',
        };
    }

    /** Solo hombres pueden tener privilegio de anciano o siervo ministerial. */
    public function requiresMale(): bool
    {
        return $this !== self::Publisher;
    }

    /**
     * @return array<string, string> [valor => etiqueta]
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $p) => [$p->value => $p->label()])
            ->all();
    }
}
