<?php

namespace App\Enums;

/**
 * Estados de un usuario. Valores en inglés (código), etiquetas en español (UI).
 */
enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Inactive => 'Inactivo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
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
