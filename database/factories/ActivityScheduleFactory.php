<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Church;
use App\Enums\DayPattern;
use App\Models\ActivitySchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivitySchedule>
 */
class ActivityScheduleFactory extends Factory
{
    protected $model = ActivitySchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'team_id' => Team::factory(),
            'type' => 'mass',
            'activity_type_description' => 'mass',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'language' => 'English',
            'day_pattern' => DayPattern::Sunday->value,
            'days_of_week' => null,
            'week_of_month' => null,
            'day_of_week' => null,
            'has_day_variations' => false,
            'schedule_period_id' => null,
            'effective_from' => null,
            'effective_until' => null,
        ];
    }

    /**
     * Create a weekday schedule (Mon-Fri).
     */
    public function weekdays(string $type = 'mass'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_pattern' => DayPattern::Weekdays->value,
            'days_of_week' => null,
            'week_of_month' => null,
            'day_of_week' => null,
        ]);
    }

    /**
     * Create a Saturday schedule.
     */
    public function saturday(string $type = 'mass'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_pattern' => DayPattern::Saturday->value,
            'days_of_week' => null,
            'week_of_month' => null,
            'day_of_week' => null,
        ]);
    }

    /**
     * Create a Sunday schedule.
     */
    public function sunday(string $type = 'mass'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_pattern' => DayPattern::Sunday->value,
            'days_of_week' => null,
            'week_of_month' => null,
            'day_of_week' => null,
        ]);
    }

    /**
     * Create a daily schedule.
     */
    public function daily(string $type = 'mass'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_pattern' => DayPattern::Daily->value,
            'days_of_week' => null,
            'week_of_month' => null,
            'day_of_week' => null,
        ]);
    }

    /**
     * Create a schedule for specific days.
     *
     * @param  array<string>  $days
     */
    public function specificDays(array $days, string $type = 'mass'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_pattern' => DayPattern::Specific->value,
            'days_of_week' => $days,
            'week_of_month' => null,
            'day_of_week' => null,
        ]);
    }

    /**
     * Create a monthly nth weekday schedule (e.g., first Friday of the month).
     */
    public function monthlyNth(string $dayOfWeek = 'Friday', int $weekOfMonth = ActivitySchedule::WEEK_FIRST, string $type = 'mass'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_pattern' => DayPattern::MonthlyNth->value,
            'days_of_week' => null,
            'week_of_month' => $weekOfMonth,
            'day_of_week' => $dayOfWeek,
        ]);
    }

    /**
     * Mark the schedule as having day variations.
     */
    public function withDayVariations(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_day_variations' => true,
        ]);
    }

    /**
     * Set effective dates for the schedule.
     */
    public function effectiveBetween(?string $from, ?string $until): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_from' => $from,
            'effective_until' => $until,
        ]);
    }

    /**
     * Set the start time for the schedule.
     */
    public function startingAt(string $time): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $time,
        ]);
    }

    /**
     * Set the language for the schedule.
     */
    public function inLanguage(string $language): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => $language,
        ]);
    }

    /**
     * Set the activity type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'activity_type_description' => $type,
        ]);
    }
}
