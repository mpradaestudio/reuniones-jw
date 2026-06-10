<?php

namespace App\Http\Requests\Congregation;

use App\Enums\CongregationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCongregationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('congregations.toggle-status');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'estado' => ['required', new Enum(CongregationStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'Debe indicar el nuevo estado.',
        ];
    }
}
