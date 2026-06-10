<?php

namespace App\Http\Requests\Roles;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para eliminar un rol.
 *
 * La autorización ya bloquea los roles de sistema (RolePolicy::delete). Si el
 * rol tiene usuarios asignados, se exige un rol destino (`reassign_to`) para
 * reasignarlos antes de eliminar; esa comprobación se realiza en el controlador
 * (necesita contar usuarios).
 */
class DestroyRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $role instanceof Role
            && ($this->user()?->can('delete', $role) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Role $role */
        $role = $this->route('role');

        return [
            'reassign_to' => [
                'nullable', 'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
                Rule::notIn([$role->name]),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reassign_to.not_in' => 'El rol destino debe ser distinto del rol que se elimina.',
        ];
    }
}
