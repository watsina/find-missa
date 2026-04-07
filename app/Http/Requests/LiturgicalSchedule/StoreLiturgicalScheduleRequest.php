<?php

namespace App\Http\Requests\LiturgicalSchedule;

use App\Models\Church;
use App\Enums\ActivityType;
use App\Rules\ExistsInTeam;
use Illuminate\Validation\Rule;
use App\Enums\ActivityTypeDescription;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateActivityTypeDescription;

class StoreLiturgicalScheduleRequest extends FormRequest
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
            'church_id' => ['required', 'integer', new ExistsInTeam(Church::class)],
            'holiday_id' => ['required', 'integer', 'exists:holidays,id'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'replaces_regular_schedule' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // Times for the liturgical schedule
            'times' => ['required', 'array', 'min:1'],
            'times.*.type' => ['required', Rule::enum(ActivityType::class)],
            'times.*.activity_type_description' => ['nullable', Rule::enum(ActivityTypeDescription::class), new ValidateActivityTypeDescription],
            'times.*.start_time' => ['required', 'date_format:H:i'],
            'times.*.end_time' => [
                'required_if:times.*.type,confession,adoration',
                'nullable',
                'date_format:H:i',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (empty($value)) {
                        return;
                    }

                    preg_match('/times\.(\d+)\.end_time/', $attribute, $matches);
                    $index = $matches[1] ?? null;

                    if ($index !== null) {
                        $startTime = $this->input("times.{$index}.start_time");
                        if ($startTime && $value <= $startTime) {
                            $fail('The end time must be after the start time.');
                        }
                    }
                },
            ],
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
            'times.*.end_time.required_if' => 'The end time is required for confession and adoration activities.',
        ];
    }
}
