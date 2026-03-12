<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Church;
use App\Models\Holiday;
use App\Models\LiturgicalSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LiturgicalSchedule>
 */
class LiturgicalScheduleFactory extends Factory
{
    protected $model = LiturgicalSchedule::class;

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
            'holiday_id' => Holiday::factory(),
            'year' => null,
            'replaces_regular_schedule' => true,
            'notes' => null,
        ];
    }

    /**
     * Make the schedule apply to a specific year only.
     */
    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
        ]);
    }

    /**
     * Make the schedule recurring (applies every year).
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => null,
        ]);
    }

    /**
     * Set the schedule to add to regular schedule instead of replacing.
     */
    public function additive(): static
    {
        return $this->state(fn (array $attributes) => [
            'replaces_regular_schedule' => false,
        ]);
    }

    /**
     * Set the schedule to replace regular schedule (default).
     */
    public function replacing(): static
    {
        return $this->state(fn (array $attributes) => [
            'replaces_regular_schedule' => true,
        ]);
    }

    /**
     * Add notes to the schedule.
     */
    public function withNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $notes,
        ]);
    }
}
