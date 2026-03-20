<?php

namespace App\Http\Requests\AdorationChapel;

use App\Enums\DayPattern;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\Models\AdorationChapelSchedule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdorationChapelScheduleRequest extends FormRequest
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

            // Day details (variations)
            'day_details' => ['nullable', 'array'],
            'day_details.*.day_of_week' => ['required', 'string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'day_details.*.opens_at' => ['nullable', 'date_format:H:i'],
            'day_details.*.closes_at' => ['nullable', 'date_format:H:i'],
            'day_details.*.is_open_24_hours' => ['boolean'],
            'day_details.*.is_closed' => ['boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Validate required times before overlap check
            $this->validateRequiredTimes($validator);

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateNoOverlap($validator);
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

    /**
     * Validate that the new schedule does not overlap with existing schedules.
     */
    protected function validateNoOverlap(Validator $validator): void
    {
        $church = $this->route('church');
        $chapel = $church?->adorationChapel;

        if (! $chapel) {
            return;
        }

        $newSchedule = new AdorationChapelSchedule($this->validated());
        $newDays = $newSchedule->getCoveredDays();

        if (empty($newDays)) {
            return;
        }

        $existingSchedules = $chapel->schedules()->with('dayDetails')->get();

        foreach ($existingSchedules as $existing) {
            $existingDays = $existing->getCoveredDays();
            $overlappingDays = array_intersect($newDays, $existingDays);

            if (empty($overlappingDays)) {
                continue;
            }

            // Check time overlap for each overlapping day
            foreach ($overlappingDays as $day) {
                if ($this->hasTimeOverlapForDay($newSchedule, $existing, $day)) {
                    $validator->errors()->add(
                        'day_pattern',
                        "This schedule overlaps with an existing {$existing->getFormattedSchedule()['pattern']} schedule on {$day}."
                    );

                    return;
                }
            }
        }
    }

    /**
     * Check if two schedules have overlapping times for a specific day.
     */
    protected function hasTimeOverlapForDay(AdorationChapelSchedule $new, AdorationChapelSchedule $existing, string $day): bool
    {
        $newHours = $this->getScheduleHoursForDay($new, $day);
        $existingHours = $existing->getResolvedHoursForDay($day);

        // If either is closed, no overlap (closed claims no time)
        if ($newHours['is_closed'] || $existingHours['is_closed']) {
            return false;
        }

        // If either is 24 hours, there's definitely overlap
        if ($newHours['is_open_24_hours'] || $existingHours['is_open_24_hours']) {
            return true;
        }

        // Both have specific time ranges — check intersection
        if (! $newHours['opens_at'] || ! $newHours['closes_at'] || ! $existingHours['opens_at'] || ! $existingHours['closes_at']) {
            return false;
        }

        return $newHours['opens_at'] < $existingHours['closes_at']
            && $newHours['closes_at'] > $existingHours['opens_at'];
    }

    /**
     * Get the resolved hours for a schedule on a specific day.
     * For new schedules that don't have persisted day details, use request data.
     *
     * @return array{opens_at: string|null, closes_at: string|null, is_open_24_hours: bool, is_closed: bool}
     */
    protected function getScheduleHoursForDay(AdorationChapelSchedule $schedule, string $day): array
    {
        $dayDetails = $this->input('day_details', []);

        foreach ($dayDetails as $detail) {
            $detailDay = $detail['day_of_week'] ?? '';
            if (strcasecmp($detailDay, $day) === 0) {
                return [
                    'opens_at' => $detail['opens_at'] ?? $schedule->opens_at,
                    'closes_at' => $detail['closes_at'] ?? $schedule->closes_at,
                    'is_open_24_hours' => $detail['is_open_24_hours'] ?? $schedule->is_open_24_hours ?? false,
                    'is_closed' => $detail['is_closed'] ?? $schedule->is_closed ?? false,
                ];
            }
        }

        return [
            'opens_at' => $schedule->opens_at,
            'closes_at' => $schedule->closes_at,
            'is_open_24_hours' => $schedule->is_open_24_hours ?? false,
            'is_closed' => $schedule->is_closed ?? false,
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
            'days_of_week.required_if' => 'Please select at least one day of the week.',
            'days_of_week.min' => 'Please select at least one day of the week.',
            'closes_at.after' => 'The closing time must be after the opening time.',
        ];
    }
}
