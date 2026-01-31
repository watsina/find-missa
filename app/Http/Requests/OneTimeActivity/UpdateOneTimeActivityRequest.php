<?php

namespace App\Http\Requests\OneTimeActivity;

use App\Enums\ActivityType;
use Illuminate\Validation\Rule;
use App\Enums\ActivityTypeDescription;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateActivityTypeDescription;

class UpdateOneTimeActivityRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', Rule::enum(ActivityType::class)],
            'activity_type_description' => ['nullable', Rule::enum(ActivityTypeDescription::class), new ValidateActivityTypeDescription],
            'date' => ['sometimes', 'required', 'date'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'language' => ['sometimes', 'required', 'string', 'max:100'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
