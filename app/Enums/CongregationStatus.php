<?php

namespace App\Enums;

/**
 * Estados de una congregación. Valores en inglés (código), etiquetas en español (UI).
 */
enum CongregationStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    /**
     * Etiqueta legible en español para la interfaz.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::Inactive => 'Inactiva',
            self::Suspended => 'Suspendida',
        };
    }

    /**
     * Color sugerido para badges en la UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
            self::Suspended => 'amber',
        };
    }

    /**
     * @return array<string, string> [valor => etiqueta]
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
