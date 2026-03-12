<?php

namespace Database\Factories;

use App\Models\AdorationChapelSchedule;
use App\Models\AdorationChapelScheduleDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdorationChapelScheduleDetail>
 */
class AdorationChapelScheduleDetailFactory extends Factory
{
    protected $model = AdorationChapelScheduleDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'adoration_chapel_schedule_id' => AdorationChapelSchedule::factory(),
            'day_of_week' => 'Monday',
            'opens_at' => null,
            'closes_at' => null,
            'is_open_24_hours' => false,
            'is_closed' => false,
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
     * Set opening hours for this day.
     */
    public function hours(string $opens, string $closes): static
    {
        return $this->state(fn (array $attributes) => [
            'opens_at' => $opens,
            'closes_at' => $closes,
            'is_open_24_hours' => false,
            'is_closed' => false,
        ]);
    }

    /**
     * Mark this day as open 24 hours.
     */
    public function open24Hours(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open_24_hours' => true,
            'opens_at' => null,
            'closes_at' => null,
            'is_closed' => false,
        ]);
    }

    /**
     * Mark this day as closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_closed' => true,
            'opens_at' => null,
            'closes_at' => null,
            'is_open_24_hours' => false,
        ]);
    }
}
