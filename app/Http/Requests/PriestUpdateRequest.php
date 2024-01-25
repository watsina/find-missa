<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriestUpdateRequest extends FormRequest
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
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'parish_type' => ['required', 'string'],
            'parish_id' => ['nullable', 'integer', 'exists:parishes,id'],
            'meta' => ['nullable', 'json'],
        ];
    }
}
