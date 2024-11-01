<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParishUpdateRequest extends FormRequest
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
            'diocese_id' => ['nullable', 'integer', 'exists:dioceses,id'],
            'office_address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
        ];
    }
}
