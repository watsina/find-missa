<?php

namespace App\Http\Requests\AdorationChapel;

use App\Enums\DayPattern;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AdorationChapelExceptionRequest extends FormRequest
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
            'reason' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],

            // Schedule override fields
            'day_pattern' => ['required', Rule::enum(DayPattern::class)->except([DayPattern::MonthlyNth])],
            'days_of_week' => [
                'required_if:day_pattern,specific',
                'exclude_unless:day_pattern,specific',
                'nullable',
                'array',
                'min:1',
            ],
            'days_of_week.*' => ['string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i', 'after:opens_at'],
            'is_open_24_hours' => ['boolean'],
            'is_closed' => ['boolean'],
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
            'days_of_week.required_if' => 'Please select at least one day of the week.',
            'days_of_week.min' => 'Please select at least one day of the week.',
            'closes_at.after' => 'The closing time must be after the opening time.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateRequiredTimes($validator);
        });
    }

    /**
     * Validate that opens_at and closes_at are provided when required.
     */
    protected function validateRequiredTimes(Validator $validator): void
    {
        if ($this->boolean('is_open_24_hours') || $this->boolean('is_closed')) {
            return;
        }

        if (empty($this->input('opens_at'))) {
            $validator->errors()->add('opens_at', 'The opening time is required unless the chapel is open 24 hours or closed.');
        }

        if (empty($this->input('closes_at'))) {
            $validator->errors()->add('closes_at', 'The closing time is required unless the chapel is open 24 hours or closed.');
        }
    }
}
