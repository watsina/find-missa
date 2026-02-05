<?php

namespace App\Http\Requests;

use App\Models\Diocese;
use App\Rules\ExistsInTeam;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ParishRequest extends FormRequest
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
            'diocese_id' => ['required', 'integer', new ExistsInTeam(Diocese::class)],
            'name' => [
                'required',
                'string',
                Rule::unique('parishes', 'name')
                    ->where('diocese_id', $this->diocese_id)
                    ->ignore($this->parish ?? ''),
            ],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'office_address' => ['nullable', 'string'],
            'city' => ['required', 'string'],
            'district' => ['required', 'string'],
        ];
    }
}
