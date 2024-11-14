<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SubParishRequest extends FormRequest
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
            'parish_id' => ['required', 'integer', 'exists:parishes,id'],
            'name' => [
                'required',
                'string',
                Rule::unique('sub_parishes', 'name')
                    ->where('parish_id', $this->parish_id)
                    ->ignore($this->subParish ?? ''),
            ],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'city' => ['required', 'string'],
            'office_address' => ['nullable', 'string'],
        ];
    }
}
