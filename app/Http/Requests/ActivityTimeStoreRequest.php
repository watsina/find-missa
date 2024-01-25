<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivityTimeStoreRequest extends FormRequest
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
            'type' => ['required', 'string'],
            'start_time' => ['required', 'string', 'max:100'],
            'end_time' => ['required', 'string', 'max:100'],
            'language' => ['required', 'string', 'max:100'],
            'church_id' => ['nullable', 'integer', 'exists:churches,id'],
            'occurence' => ['required', 'string', 'max:100'],
            'date' => ['nullable'],
            'day_of_week' => ['nullable', 'string', 'max:100'],
        ];
    }
}
