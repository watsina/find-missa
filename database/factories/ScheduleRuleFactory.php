<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Church;
use App\Enums\ScheduleScope;
use App\Models\ScheduleRule;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleRuleCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduleRule>
 */
class ScheduleRuleFactory extends Factory
{
    protected $model = ScheduleRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'scope_type' => ScheduleScope::Church->value,
            'scope_id' => Church::factory(),
            'team_id' => Team::factory(),
            'activity_type' => null,
            'target_times' => null,
            'target_day_patterns' => null,
            'condition_type' => ScheduleRuleCondition::PublicHoliday->value,
            'condition_holidays' => null,
            'action' => ScheduleAction::Exclude->value,
            'is_active' => true,
            'priority' => 0,
        ];
    }

    /**
     * Create a rule for a church.
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
     * Create a rule for a diocese.
     */
    public function forDiocese(int $dioceseId): static
    {
        return $this->state(fn (array $attributes) => [
            'scope_type' => ScheduleScope::Diocese->value,
            'scope_id' => $dioceseId,
        ]);
    }

    /**
     * Create a public holiday rule.
     */
    public function onPublicHolidays(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_type' => ScheduleRuleCondition::PublicHoliday->value,
            'condition_holidays' => null,
        ]);
    }

    /**
     * Create a liturgical day rule.
     *
     * @param  array<string>  $holidaySlugs
     */
    public function onLiturgicalDays(array $holidaySlugs): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_type' => ScheduleRuleCondition::LiturgicalDay->value,
            'condition_holidays' => $holidaySlugs,
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
        ]);
    }

    /**
     * Mark the rule as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set the priority.
     */
    public function withPriority(int $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $priority,
        ]);
    }
}
