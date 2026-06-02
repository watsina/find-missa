<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchActivitiesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:mass,confession,adoration'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'church_name' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
        ];
    }
}
