<?php

namespace App\Http\Requests\AdorationChapel;

use App\Enums\DayPattern;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdorationChapelRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],

            // Schedules
            'schedules' => ['nullable', 'array'],
            'schedules.*.day_pattern' => ['required', Rule::enum(DayPattern::class)],
            'schedules.*.days_of_week' => [
                'required_if:schedules.*.day_pattern,specific',
                'nullable',
                'array',
                'min:1',
            ],
            'schedules.*.days_of_week.*' => ['string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'schedules.*.opens_at' => ['nullable', 'date_format:H:i'],
            'schedules.*.closes_at' => ['nullable', 'date_format:H:i'],
            'schedules.*.is_open_24_hours' => ['boolean'],
            'schedules.*.is_closed' => ['boolean'],
            'schedules.*.effective_from' => ['nullable', 'date'],
            'schedules.*.effective_until' => ['nullable', 'date', 'after_or_equal:schedules.*.effective_from'],

            // Schedule day details (variations within a schedule)
            'schedules.*.day_details' => ['nullable', 'array'],
            'schedules.*.day_details.*.day_of_week' => ['required', 'string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'schedules.*.day_details.*.opens_at' => ['nullable', 'date_format:H:i'],
            'schedules.*.day_details.*.closes_at' => ['nullable', 'date_format:H:i'],
            'schedules.*.day_details.*.is_open_24_hours' => ['boolean'],
            'schedules.*.day_details.*.is_closed' => ['boolean'],
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
            'schedules.*.days_of_week.required_if' => 'Please select at least one day of the week for specific day patterns.',
            'schedules.*.days_of_week.min' => 'Please select at least one day of the week.',
            'schedules.*.effective_until.after_or_equal' => 'The effective until date must be on or after the effective from date.',
        ];
    }
}
