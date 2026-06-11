<?php

namespace App\Enums;

/**
 * Estado operativo de un publicador en la congregación.
 * Valores en inglés (código), etiquetas en español (UI).
 */
enum PublisherStatus: string
{
    case Active = 'activo';
    case Irregular = 'irregular';
    case Inactive = 'inactivo';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Irregular => 'Irregular',
            self::Inactive => 'Inactivo',
        };
    }

    /** Clase de badge Bootstrap 5 para la UI. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'text-bg-success',
            self::Irregular => 'text-bg-warning',
            self::Inactive => 'text-bg-secondary',
        };
    }

    /**
     * @return array<string, string> [valor => etiqueta]
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
