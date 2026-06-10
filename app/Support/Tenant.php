<?php

namespace App\Support;

use App\Models\Congregation;

/**
 * Mantiene la congregación activa (tenant) resuelta por subdominio durante la
 * petición. Se registra como singleton en el contenedor (AppServiceProvider).
 */
class Tenant
{
    protected ?Congregation $congregation = null;

    public function set(?Congregation $congregation): void
    {
        $this->congregation = $congregation;
    }

    public function get(): ?Congregation
    {
        return $this->congregation;
    }

    public function id(): ?int
    {
        return $this->congregation?->id;
    }

    public function hasCongregation(): bool
    {
        return $this->congregation !== null;
    }

    public function clear(): void
    {
        $this->congregation = null;
    }
}
