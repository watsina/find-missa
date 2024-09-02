<?php

namespace App\Http\Requests;

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
            'type' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-99.9999999,99.9999999'],
            'longitude' => ['nullable', 'numeric', 'between:-99.9999999,99.9999999'],
            'street_address' => ['nullable', 'string'],
            'postal_address' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string'],
            'parish_type' => ['nullable', 'string'],
            'parish_id' => ['nullable', 'integer', 'exists:parishes,id'],
            'priest_id' => ['nullable', 'integer', 'exists:priests,id'],
        ];
    }
}
