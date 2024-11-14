<?php

namespace App\Http\Requests;

use App\Enums\ChurchType;
use Illuminate\Validation\Rule;
use App\Rules\ValidateParishableId;
use Illuminate\Foundation\Http\FormRequest;

class ChurchRequest extends FormRequest
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
            'parishable_id' => ['required', 'integer', new ValidateParishableId],
            'type' => ['required', Rule::enum(ChurchType::class)],
            // church name should be unique in parish or subParish
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('churches', 'name')
                    ->where('parishable_id', $this->parishable_id)
                    ->where('parishable_type', $this->type === 'sub-parish' ? 'subParish' : 'parish')
                    ->ignore($this->church ?? ''),
            ],
            'latitude' => ['nullable', 'numeric', 'between:-90.9999999,90.9999999'],
            'longitude' => ['nullable', 'numeric', 'between:-180.9999999,180.9999999'],
            'street_address' => ['nullable', 'string'],
            'city' => ['required', 'string'],
            'postal_address' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string'],
            'priest_id' => ['nullable', 'integer', 'exists:priests,id'],
        ];
    }
}
