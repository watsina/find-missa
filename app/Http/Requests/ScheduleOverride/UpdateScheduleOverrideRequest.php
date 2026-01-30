<?php

namespace App\Http\Requests\ScheduleOverride;

use App\Enums\DayPattern;
use App\Enums\ActivityType;
use App\Enums\ScheduleScope;
use App\Enums\ScheduleAction;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleOverrideRequest extends FormRequest
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
            'scope_type' => ['sometimes', 'required', Rule::enum(ScheduleScope::class)],
            'scope_id' => ['sometimes', 'required', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after_or_equal:start_date'],
            'action' => ['sometimes', 'required', Rule::enum(ScheduleAction::class)],
            'activity_type' => ['nullable', Rule::enum(ActivityType::class)],
            'target_times' => ['nullable', 'array'],
            'target_times.*' => ['date_format:H:i'],
            'target_day_patterns' => ['nullable', 'array'],
            'target_day_patterns.*' => [Rule::enum(DayPattern::class)],
            'replacement_times' => [
                'required_if:action,modify',
                'nullable',
                'array',
            ],
            'replacement_times.*.original' => ['required', 'date_format:H:i'],
            'replacement_times.*.replacement' => ['required', 'date_format:H:i'],
            'is_active' => ['boolean'],
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
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'replacement_times.required_if' => 'Replacement times are required for modify action.',
        ];
    }
}
