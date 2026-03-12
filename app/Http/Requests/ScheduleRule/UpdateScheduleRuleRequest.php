<?php

namespace App\Http\Requests\ScheduleRule;

use App\Enums\DayPattern;
use App\Enums\ActivityType;
use App\Enums\ScheduleScope;
use App\Enums\ScheduleAction;
use Illuminate\Validation\Rule;
use App\Enums\ScheduleRuleCondition;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRuleRequest extends FormRequest
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
            'condition_type' => ['sometimes', 'required', Rule::enum(ScheduleRuleCondition::class)],
            'condition_holidays' => [
                'required_if:condition_type,liturgical_day',
                'nullable',
                'array',
            ],
            'condition_holidays.*' => ['string', 'exists:holidays,slug'],
            'action' => ['sometimes', 'required', Rule::enum(ScheduleAction::class)],
            'activity_type' => ['nullable', Rule::enum(ActivityType::class)],
            'target_times' => ['nullable', 'array'],
            'target_times.*' => ['date_format:H:i'],
            'target_day_patterns' => ['nullable', 'array'],
            'target_day_patterns.*' => [Rule::enum(DayPattern::class)],
            'priority' => ['integer', 'min:0', 'max:100'],
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
            'condition_holidays.required_if' => 'Please specify which holidays this rule applies to.',
        ];
    }
}
