<?php

namespace App\Http\Requests\ActivitySchedule;

use App\Enums\DayPattern;
use App\Enums\ActivityType;
use Illuminate\Validation\Rule;
use App\Models\ActivitySchedule;
use App\Enums\ActivityTypeDescription;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateActivityTypeDescription;

class StoreActivityScheduleRequest extends FormRequest
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
            'church_id' => ['required', 'integer', 'exists:churches,id'],
            'type' => ['required', Rule::enum(ActivityType::class)],
            'activity_type_description' => ['nullable', Rule::enum(ActivityTypeDescription::class), new ValidateActivityTypeDescription],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'language' => ['required', 'string', 'max:100'],
            'day_pattern' => ['required', Rule::enum(DayPattern::class)],
            'days_of_week' => [
                'required_if:day_pattern,specific',
                'exclude_unless:day_pattern,specific',
                'nullable',
                'array',
                'min:1',
            ],
            'days_of_week.*' => ['string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'week_of_month' => [
                'required_if:day_pattern,monthly-nth',
                'exclude_unless:day_pattern,monthly-nth',
                'nullable',
                'integer',
                Rule::in(array_keys(ActivitySchedule::weekOfMonthOptions())),
            ],
            'day_of_week' => [
                'required_if:day_pattern,monthly-nth',
                'exclude_unless:day_pattern,monthly-nth',
                'nullable',
                'string',
                Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            ],
            'has_day_variations' => ['boolean'],
            'schedule_period_id' => ['nullable', 'integer', 'exists:schedule_periods,id'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],

            // Day details (variations)
            'day_details' => ['nullable', 'array'],
            'day_details.*.day_of_week' => ['required', 'string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'day_details.*.start_time' => ['nullable', 'date_format:H:i'],
            'day_details.*.end_time' => ['nullable', 'date_format:H:i'],
            'day_details.*.language' => ['nullable', 'string', 'max:100'],
            'day_details.*.activity_type_description' => ['nullable', Rule::enum(ActivityTypeDescription::class)],
            'day_details.*.is_skipped' => ['boolean'],

            // Tags
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:activity_tags,id'],
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
            'week_of_month.required_if' => 'Please select which week of the month.',
            'day_of_week.required_if' => 'Please select a day of the week.',
            'days_of_week.required_if' => 'Please select at least one day of the week.',
            'days_of_week.min' => 'Please select at least one day of the week.',
        ];
    }
}
