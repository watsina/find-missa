<?php

namespace App\Http\Requests\LiturgicalSchedule;

use App\Enums\ActivityType;
use Illuminate\Validation\Rule;
use App\Enums\ActivityTypeDescription;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateActivityTypeDescription;

class UpdateLiturgicalScheduleRequest extends FormRequest
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
            'holiday_id' => ['sometimes', 'required', 'integer', 'exists:holidays,id'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'replaces_regular_schedule' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // Times for the liturgical schedule
            'times' => ['sometimes', 'required', 'array', 'min:1'],
            'times.*.id' => ['nullable', 'integer', 'exists:liturgical_schedule_times,id'],
            'times.*.type' => ['required', Rule::enum(ActivityType::class)],
            'times.*.activity_type_description' => ['nullable', Rule::enum(ActivityTypeDescription::class), new ValidateActivityTypeDescription],
            'times.*.start_time' => ['required', 'date_format:H:i'],
            'times.*.end_time' => ['nullable', 'date_format:H:i', 'after:times.*.start_time'],
            'times.*.language' => ['required', 'string', 'max:100'],
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
            'times.required' => 'Please add at least one time for this liturgical schedule.',
            'times.min' => 'Please add at least one time for this liturgical schedule.',
            'times.*.end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
