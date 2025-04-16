<?php

namespace App\Http\Requests;

use App\Rules\DayOfWeek;
use App\Enums\ActivityType;
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
            'church_id' => ['nullable', 'integer', 'exists:churches,id'],
            'occurrence' => ['required', Rule::enum(ActivityOccurrence::class)],
            'date' => ['required_if:occurrence,one-time', 'exclude_unless:occurrence,one-time', 'nullable', 'date'],
        ];
    }

    protected function createRules(): array
    {
        return array_merge($this->commonRules(), [
            'starting_times' => ['required_if:start_time,null', 'exclude_unless:start_time,null', 'nullable', 'array'],
            'starting_times.*' => ['required', 'date_format:H:i'],
            'day_of_week' => [
                Rule::requiredIf(function () {
                    return $this->occurrence === 'weekly' && ! $this->days_of_week;
                }),
                'exclude_unless:occurrence,weekly',
                'nullable',
                new DayOfWeek,
            ],
            'days_of_week' => [
                Rule::requiredIf(function () {
                    return $this->occurrence === 'weekly' && ! $this->day_of_week;
                }),
                'exclude_unless:occurrence,weekly',
                'nullable',
                'array',
            ],
            'days_of_week.*' => ['required', 'string',  new DayOfWeek],
        ]);
    }

    protected function updateRules(): array
    {
        return array_merge($this->commonRules(), [
            'day_of_week' => ['required_if:occurrence,weekly', 'exclude_unless:occurrence,weekly',  new DayOfWeek],
        ]);
    }

    public function messages()
    {
        return [
            'end_time.after' => 'The end time should be a time after the start time',
        ];
    }
}
