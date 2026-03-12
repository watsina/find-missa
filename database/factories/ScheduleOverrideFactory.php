<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Church;
use App\Enums\ScheduleScope;
use App\Enums\ScheduleAction;
use App\Models\ScheduleOverride;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleOverride>
 */
class ScheduleOverrideFactory extends Factory
{
    protected $model = ScheduleOverride::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'reason' => $this->faker->optional()->paragraph(),
            'scope_type' => ScheduleScope::Church->value,
            'scope_id' => Church::factory(),
            'team_id' => Team::factory(),
            'activity_type' => null,
            'target_times' => null,
            'target_day_patterns' => null,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'action' => ScheduleAction::Exclude->value,
            'replacement_times' => null,
            'is_active' => true,
        ];
    }

    /**
     * Create an override for a church.
     */
    public function forChurch(Church|int $church): static
    {
        $churchId = $church instanceof Church ? $church->id : $church;

        return $this->state(fn (array $attributes) => [
            'scope_type' => ScheduleScope::Church->value,
            'scope_id' => $churchId,
        ]);
    }

    /**
     * Create an override for a diocese.
     */
    public function forDiocese(int $dioceseId): static
    {
        return $this->state(fn (array $attributes) => [
            'scope_type' => ScheduleScope::Diocese->value,
            'scope_id' => $dioceseId,
        ]);
    }

    /**
     * Set the date range.
     */
    public function forDateRange(string $startDate, string $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Target specific times.
     *
     * @param  array<string>  $times
     */
    public function targetingTimes(array $times): static
    {
        return $this->state(fn (array $attributes) => [
            'target_times' => $times,
        ]);
    }

    /**
     * Target specific day patterns.
     *
     * @param  array<string>  $patterns
     */
    public function targetingDayPatterns(array $patterns): static
    {
        return $this->state(fn (array $attributes) => [
            'target_day_patterns' => $patterns,
        ]);
    }

    /**
     * Target a specific activity type.
     */
    public function forActivityType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => $type,
        ]);
    }

    /**
     * Set the action to exclude.
     */
    public function exclude(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ScheduleAction::Exclude->value,
            'replacement_times' => null,
        ]);
    }

    /**
     * Set the action to modify with replacement times.
     *
     * @param  array<array{original: string, replacement: string}>  $replacements
     */
    public function modify(array $replacements): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ScheduleAction::Modify->value,
            'replacement_times' => $replacements,
        ]);
    }

    /**
     * Mark the override as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
