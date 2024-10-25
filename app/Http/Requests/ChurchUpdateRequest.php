<?php

namespace App\Http\Requests;

use App\Enums\ChurchType;
use Illuminate\Validation\Rule;
use App\Rules\ValidateParishableId;
use Illuminate\Foundation\Http\FormRequest;

class ChurchUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'type' => ['required', Rule::enum(ChurchType::class)],
            'latitude' => ['nullable', 'numeric', 'between:-99.9999999,99.9999999'],
            'longitude' => ['nullable', 'numeric', 'between:-99.9999999,99.9999999'],
            'street_address' => ['nullable', 'string'],
            'postal_address' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string'],
            'parishable_id' => ['nullable', 'integer', new ValidateParishableId],
            'priest_id' => ['nullable', 'integer', 'exists:priests,id'],
        ];
    }
}
