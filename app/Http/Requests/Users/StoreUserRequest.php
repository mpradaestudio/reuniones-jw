<?php

namespace App\Http\Requests\Users;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/**
 * Validación para el alta de usuarios.
 *
 * Decisiones aplicadas:
 *  - Email ÚNICO GLOBAL: la unicidad NO se acota por congregación.
 *  - UN ROL POR USUARIO: `role` es un único valor (no un array).
 *  - Un usuario que no sea SuperAdministrador no puede asignar ese rol global.
 */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            // Email único a nivel global (sin acotar por congregación).
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'estado' => ['required', Rule::enum(UserStatus::class)],
            // Un único rol por usuario (no un array).
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            // Solo lo usa el SuperAdministrador; el controlador decide el valor final.
            'congregation_id' => ['nullable', 'integer', 'exists:congregations,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $role = $this->input('role');
            $superAdminRole = config('tenancy.super_admin_role', 'SuperAdministrador');

            if ($role === $superAdminRole && ! ($this->user()?->isSuperAdmin() ?? false)) {
                $validator->errors()->add('role', 'No tiene permiso para asignar el rol SuperAdministrador.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
        }
    }
}
