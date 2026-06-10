<?php

namespace App\Http\Requests\Congregation;

use App\Enums\CongregationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreCongregationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('congregations.create');
    }

    /**
     * Normaliza el subdominio antes de validar.
     */
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
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'subdominio' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('congregations', 'subdominio'),
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

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'subdominio' => 'subdominio',
            'estado' => 'estado',
        ];
    }
}
