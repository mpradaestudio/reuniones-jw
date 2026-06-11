<?php

namespace App\Http\Requests\Publishers;

use App\Enums\PublisherPrivilege;
use App\Enums\PublisherStatus;
use App\Models\Publisher;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePublisherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Publisher::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $actor = $this->user();

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'genero' => ['required', Rule::in(['masculino', 'femenino'])],
            'fecha_bautismo' => ['nullable', 'date', 'before_or_equal:today'],
            'estado' => ['required', new Enum(PublisherStatus::class)],
            'privilegio' => [
                'required',
                new Enum(PublisherPrivilege::class),
                // Anciano y siervo ministerial solo para hombres.
                function (string $attribute, mixed $value, Closure $fail) {
                    $genero = $this->input('genero');
                    $privilege = PublisherPrivilege::tryFrom($value);
                    if ($privilege && $privilege->requiresMale() && $genero !== 'masculino') {
                        $fail('El privilegio de '.($privilege->label()).' solo puede asignarse a un hombre.');
                    }
                },
            ],
            'es_nombrado' => ['boolean'],
            // user_id: opcional; si se proporciona debe existir en la misma congregación.
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($actor) {
                    $congregationId = $actor->isSuperAdmin()
                        ? $this->input('congregation_id')
                        : $actor->congregation_id;

                    if ($congregationId) {
                        $query->where('congregation_id', $congregationId);
                    }
                }),
                // No puede vincularse a un User que ya tiene otro publicador.
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($value === null) {
                        return;
                    }
                    $exists = Publisher::withoutGlobalScopes()
                        ->where('user_id', $value)
                        ->exists();
                    if ($exists) {
                        $fail('Este usuario ya está vinculado a otro publicador.');
                    }
                },
            ],
        ];
    }
}
