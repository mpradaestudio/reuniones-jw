<?php

namespace App\Http\Requests\Congregation;

use App\Enums\CongregationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCongregationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('congregations.update');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('subdominio')) {
            $this->merge([
                'subdominio' => strtolower(trim((string) $this->input('subdominio'))),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $congregationId = $this->route('congregation')->id;

        return [
            'nombre' => ['required', 'string', 'max:150'],
            'subdominio' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('congregations', 'subdominio')->ignore($congregationId),
            ],
            'estado' => ['required', new Enum(CongregationStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subdominio.regex' => 'El subdominio solo puede contener minúsculas, números y guiones (sin guiones al inicio o final).',
            'subdominio.unique' => 'Ese subdominio ya está en uso.',
        ];
    }
}
