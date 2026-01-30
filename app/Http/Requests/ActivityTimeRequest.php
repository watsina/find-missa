<?php

namespace App\Http\Requests;

use App\Models\Church;
use App\Rules\DayOfWeek;
use App\Enums\ActivityType;
use App\Rules\ExistsInTeam;
use App\Models\ActivityTime;
use Illuminate\Validation\Rule;
use App\Enums\ActivityOccurrence;
use App\Enums\ActivityTypeDescription;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateActivityTypeDescription;

class ActivityTimeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        if ($this->method() === 'POST') {
            return $this->createRules();
        }

        return $this->updateRules();
    }

    protected function commonRules(): array
    {
        return [
            'type' => ['required', Rule::enum(ActivityType::class)],
            'activity_type_description' => ['required', Rule::enum(ActivityTypeDescription::class), new ValidateActivityTypeDescription],
            'start_time' => ['required_if:starting_times,null', 'nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'language' => ['required', 'string', 'max:100'],
            'church_id' => ['nullable', 'integer', new ExistsInTeam(Church::class)],
            'occurrence' => ['required', Rule::enum(ActivityOccurrence::class)],
            'date' => ['required_if:occurrence,one-time', 'exclude_unless:occurrence,one-time', 'nullable', 'date'],
            'schedule_period_id' => ['nullable', 'integer', 'exists:schedule_periods,id'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'week_of_month' => [
                'required_if:occurrence,monthly-nth-weekday',
                'exclude_unless:occurrence,monthly-nth-weekday',
                'nullable',
                'integer',
                Rule::in(array_keys(ActivityTime::weekOfMonthOptions())),
            ],
        ];
    }

    protected function createRules(): array
    {
        return array_merge($this->commonRules(), [
            'starting_times' => ['required_if:start_time,null', 'exclude_unless:start_time,null', 'nullable', 'array'],
            'starting_times.*' => ['required', 'date_format:H:i'],
            'day_of_week' => [
                Rule::requiredIf(function () {
                    return $this->requiresDayOfWeek() && ! $this->days_of_week;
                }),
                Rule::excludeIf(fn () => ! $this->requiresDayOfWeek()),
                'nullable',
                new DayOfWeek,
            ],
            'days_of_week' => [
                Rule::requiredIf(function () {
                    // Only for weekly occurrence - monthly-nth-weekday doesn't support bulk day creation
                    return $this->occurrence === 'weekly' && ! $this->day_of_week;
                }),
                'exclude_unless:occurrence,weekly',
                'nullable',
                'array',
            ],
            'days_of_week.*' => ['required', 'string', new DayOfWeek],
        ]);
    }

    protected function updateRules(): array
    {
        return array_merge($this->commonRules(), [
            'day_of_week' => [
                Rule::requiredIf(fn () => $this->requiresDayOfWeek()),
                Rule::excludeIf(fn () => ! $this->requiresDayOfWeek()),
                new DayOfWeek,
            ],
        ]);
    }

    /**
     * Check if the current occurrence type requires a day of week.
     */
    protected function requiresDayOfWeek(): bool
    {
        return in_array($this->occurrence, ['weekly', 'monthly-nth-weekday']);
    }

    public function messages()
    {
        return [
            'end_time.after' => 'The end time should be a time after the start time',
            'week_of_month.required_if' => 'Please select which week of the month',
            'week_of_month.in' => 'Invalid week of month selection',
            'day_of_week.required' => 'Please select a day of the week',
        ];
    }
}
