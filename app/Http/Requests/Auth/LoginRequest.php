<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\Tenant;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Intenta autenticar con validación estricta de tenant:
     *
     *  - Solo se autentican usuarios con estado "active".
     *  - Un usuario solo puede iniciar sesión en el subdominio de su congregación.
     *  - El SuperAdministrador es la única excepción (acceso global).
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = array_merge(
            $this->only('email', 'password'),
            ['estado' => 'active'],
        );

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        $this->enforceTenantBoundary();

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Valida que el usuario pertenezca a la congregación del subdominio actual.
     */
    protected function enforceTenantBoundary(): void
    {
        /** @var User $user */
        $user = Auth::user();

        // El SuperAdministrador puede acceder desde cualquier subdominio / área global.
        if ($user->isSuperAdmin()) {
            return;
        }

        $tenant = app(Tenant::class);

        $sameTenant = $tenant->hasCongregation()
            && (int) $user->congregation_id === (int) $tenant->id();

        if (! $sameTenant) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'No tiene acceso a esta congregación. Inicie sesión en el subdominio correcto.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos. Inténtelo de nuevo en {$seconds} segundos.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('email')).'|'.$this->ip()
        );
    }
}
