<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FullDataImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:json,txt',
                'max:102400', // 100MB max
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a JSON file to import.',
            'file.mimes' => 'The file must be a JSON file.',
            'file.max' => 'The file size must not exceed 100MB.',
        ];
    }
}
