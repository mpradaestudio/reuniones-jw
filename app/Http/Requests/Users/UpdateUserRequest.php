<?php

namespace App\Http\Requests\Users;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/**
 * Validación para la edición de usuarios.
 *
 * Decisiones aplicadas:
 *  - Email ÚNICO GLOBAL: la unicidad ignora al propio usuario editado.
 *  - UN ROL POR USUARIO: `role` es un único valor.
 *  - La contraseña es opcional al editar (solo se actualiza si se envía).
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && ($this->user()?->can('update', $user) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            // Email único global, ignorando al usuario que se está editando.
            'email' => [
                'required', 'string', 'email', 'max:190',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            // Contraseña opcional al editar.
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'estado' => ['required', Rule::enum(UserStatus::class)],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
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
