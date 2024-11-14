<?php

namespace App\Http\Requests;

use App\Enums\DioceseType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class DioceseRequest extends FormRequest
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
            'name' => ['required', 'string', Rule::unique('dioceses', 'name')->ignore($this->diocese ?? '')],
            'type' => ['required', 'string', Rule::enum(DioceseType::class)],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'office_address' => ['nullable', 'string'],
            'postal_address' => ['nullable', 'string'],
        ];
    }
}
