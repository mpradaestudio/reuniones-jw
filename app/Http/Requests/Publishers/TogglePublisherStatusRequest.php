<?php

namespace App\Http\Requests\Publishers;

use App\Enums\PublisherStatus;
use App\Models\Publisher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class TogglePublisherStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Publisher $publisher */
        $publisher = $this->route('publisher');

        return $this->user()->can('toggleStatus', $publisher);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'estado' => ['required', new Enum(PublisherStatus::class)],
        ];
    }
}
