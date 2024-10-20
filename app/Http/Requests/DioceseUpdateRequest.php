<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class DioceseUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'unique:dioceses'],
            'type' => ['required', 'string', Rule::in(['diocese', 'archdiocese'])],
            'office_address' => ['nullable', 'string'],
            'postal_address' => ['nullable', 'string'],
            'country' => ['required', 'string'],
        ];
    }
}
