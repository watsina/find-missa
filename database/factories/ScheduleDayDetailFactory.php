<?php

namespace Database\Factories;

use App\Models\ActivitySchedule;
use App\Models\ScheduleDayDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduleDayDetail>
 */
class ScheduleDayDetailFactory extends Factory
{
    protected $model = ScheduleDayDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_schedule_id' => ActivitySchedule::factory(),
            'day_of_week' => 'Monday',
            'language' => null,
            'start_time' => null,
            'end_time' => null,
            'activity_type_description' => null,
            'is_skipped' => false,
        ];
    }

    /**
     * Create a detail for a specific day.
     */
    public function forDay(string $dayOfWeek): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $dayOfWeek,
        ]);
    }

    /**
     * Override the language for this day.
     */
    public function withLanguage(string $language): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => $language,
        ]);
    }

    /**
     * Override the start time for this day.
     */
    public function withStartTime(string $time): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $time,
        ]);
    }

    /**
     * Mark this day as skipped.
     */
    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_skipped' => true,
        ]);
    }

    /**
     * Override the activity type description.
     */
    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type_description' => $description,
        ]);
    }
}
