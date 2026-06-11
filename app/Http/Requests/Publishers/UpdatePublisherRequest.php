<?php

namespace App\Http\Requests\Publishers;

use App\Enums\PublisherPrivilege;
use App\Enums\PublisherStatus;
use App\Models\Publisher;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdatePublisherRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Publisher $publisher */
        $publisher = $this->route('publisher');

        return $this->user()->can('update', $publisher);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Publisher $publisher */
        $publisher = $this->route('publisher');
        $actor     = $this->user();

        return [
            'nombre'          => ['required', 'string', 'max:100'],
            'apellidos'       => ['required', 'string', 'max:100'],
            'genero'          => ['required', Rule::in(['masculino', 'femenino'])],
            'fecha_bautismo'  => ['nullable', 'date', 'before_or_equal:today'],
            'estado'          => ['required', new Enum(PublisherStatus::class)],
            'privilegio'      => [
                'required',
                new Enum(PublisherPrivilege::class),
                function (string $attribute, mixed $value, Closure $fail) {
                    $genero    = $this->input('genero');
                    $privilege = PublisherPrivilege::tryFrom($value);
                    if ($privilege && $privilege->requiresMale() && $genero !== 'masculino') {
                        $fail('El privilegio de '.($privilege->label()).' solo puede asignarse a un hombre.');
                    }
                },
            ],
            'es_nombrado'     => ['boolean'],
            'user_id'         => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($actor, $publisher) {
                    $query->where('congregation_id', $publisher->congregation_id);
                }),
                function (string $attribute, mixed $value, Closure $fail) use ($publisher) {
                    if ($value === null) {
                        return;
                    }
                    $exists = Publisher::withoutGlobalScopes()
                        ->where('user_id', $value)
                        ->where('id', '!=', $publisher->id)
                        ->exists();
                    if ($exists) {
                        $fail('Este usuario ya está vinculado a otro publicador.');
                    }
                },
            ],
        ];
    }
}
