<?php

namespace Database\Factories;

use App\Enums\DayPattern;
use App\Models\AdorationChapel;
use App\Models\AdorationChapelSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdorationChapelSchedule>
 */
class AdorationChapelScheduleFactory extends Factory
{
    protected $model = AdorationChapelSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'adoration_chapel_id' => AdorationChapel::factory(),
            'day_pattern' => DayPattern::Daily->value,
            'days_of_week' => null,
            'opens_at' => '06:00',
            'closes_at' => '22:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
            'effective_from' => null,
            'effective_until' => null,
        ];
    }

    /**
     * Create a weekday schedule (Mon-Fri).
     */
    public function weekdays(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_pattern' => DayPattern::Weekdays->value,
            'days_of_week' => null,
        ]);
    }

    /**
     * Create a Saturday schedule.
     */
    public function saturday(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_pattern' => DayPattern::Saturday->value,
            'days_of_week' => null,
        ]);
    }

    /**
     * Create a Sunday schedule.
     */
    public function sunday(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_pattern' => DayPattern::Sunday->value,
            'days_of_week' => null,
        ]);
    }

    /**
     * Create a daily schedule.
     */
    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_pattern' => DayPattern::Daily->value,
            'days_of_week' => null,
        ]);
    }

    /**
     * Create a schedule for specific days.
     *
     * @param  array<string>  $days
     */
    public function specificDays(array $days): static
    {
        return $this->state(fn (array $attributes) => [
            'day_pattern' => DayPattern::Specific->value,
            'days_of_week' => $days,
        ]);
    }

    /**
     * Mark as open 24 hours.
     */
    public function open24Hours(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open_24_hours' => true,
            'opens_at' => null,
            'closes_at' => null,
        ]);
    }

    /**
     * Mark as closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_closed' => true,
            'opens_at' => null,
            'closes_at' => null,
        ]);
    }

    /**
     * Set opening hours.
     */
    public function hours(string $opens, string $closes): static
    {
        return $this->state(fn (array $attributes) => [
            'opens_at' => $opens,
            'closes_at' => $closes,
            'is_open_24_hours' => false,
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
}
