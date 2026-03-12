<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Models\SchedulePeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchedulePeriod>
 */
class SchedulePeriodFactory extends Factory
{
    protected $model = SchedulePeriod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'is_active' => true,
            'is_recurring' => false,
            'team_id' => null,
        ];
    }

    /**
     * Indicate this is a universal schedule period (null team).
     */
    public function universal(): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => null,
        ]);
    }

    /**
     * Indicate this schedule period is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate this schedule period is recurring (repeats yearly).
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
        ]);
    }

    /**
     * Create a summer schedule period.
     */
    public function summer(?int $year = null): static
    {
        $year = $year ?? now()->year;

        return $this->state(fn (array $attributes) => [
            'name' => "Summer Schedule {$year}",
            'slug' => "summer-schedule-{$year}",
            'description' => 'Summer schedule with adjusted Mass times.',
            'start_date' => "{$year}-06-01",
            'end_date' => "{$year}-08-31",
            'is_recurring' => true,
        ]);
    }

    /**
     * Create a winter schedule period.
     */
    public function winter(?int $year = null): static
    {
        $year = $year ?? now()->year;

        return $this->state(fn (array $attributes) => [
            'name' => "Winter Schedule {$year}",
            'slug' => "winter-schedule-{$year}",
            'description' => 'Winter schedule with adjusted Mass times.',
            'start_date' => "{$year}-12-01",
            'end_date' => ($year + 1).'-02-28',
            'is_recurring' => true,
        ]);
    }

    /**
     * Create a Lent schedule period.
     */
    public function lent(?int $year = null): static
    {
        $year = $year ?? now()->year;
        // Approximate Lent dates (Ash Wednesday to Holy Saturday)
        // These are rough estimates; actual dates depend on Easter

        return $this->state(fn (array $attributes) => [
            'name' => "Lent {$year}",
            'slug' => "lent-{$year}",
            'description' => 'Lenten season schedule with additional confession times and Stations of the Cross.',
            'start_date' => "{$year}-02-14", // Approximate Ash Wednesday
            'end_date' => "{$year}-03-30", // Approximate Holy Saturday
            'is_recurring' => false,
        ]);
    }

    /**
     * Create an Advent schedule period.
     */
    public function advent(?int $year = null): static
    {
        $year = $year ?? now()->year;

        return $this->state(fn (array $attributes) => [
            'name' => "Advent {$year}",
            'slug' => "advent-{$year}",
            'description' => 'Advent season schedule with Rorate Masses and additional confession times.',
            'start_date' => "{$year}-12-01",
            'end_date' => "{$year}-12-24",
            'is_recurring' => false,
        ]);
    }

    /**
     * Create a Holy Week schedule period.
     */
    public function holyWeek(?int $year = null): static
    {
        $year = $year ?? now()->year;

        return $this->state(fn (array $attributes) => [
            'name' => "Holy Week {$year}",
            'slug' => "holy-week-{$year}",
            'description' => 'Holy Week schedule with Triduum liturgies.',
            'start_date' => "{$year}-04-13", // Approximate Palm Sunday
            'end_date' => "{$year}-04-20", // Approximate Easter Sunday
            'is_recurring' => false,
        ]);
    }

    /**
     * Create a current (active now) schedule period.
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'is_active' => true,
        ]);
    }

    /**
     * Create an upcoming (future) schedule period.
     */
    public function upcoming(): static
    {
        $startDate = now()->addMonths(1);

        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $startDate->copy()->addMonths(3)->format('Y-m-d'),
            'is_active' => true,
        ]);
    }

    /**
     * Create a past (expired) schedule period.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subMonths(6)->format('Y-m-d'),
            'end_date' => now()->subMonths(3)->format('Y-m-d'),
            'is_active' => false,
        ]);
    }

    /**
     * Set specific date range.
     */
    public function dateRange(string $startDate, string $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
