<?php

namespace App\Http\Requests;

use App\Models\Parish;
use App\Rules\ExistsInTeam;
use Illuminate\Foundation\Http\FormRequest;

class PriestStoreRequest extends FormRequest
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
            'parish_id' => ['nullable', 'integer', new ExistsInTeam(Parish::class)],
        ];
    }
}
