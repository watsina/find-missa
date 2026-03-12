<?php

namespace Database\Factories;

use App\Enums\HolidayType;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holiday>
 */
class HolidayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->optional()->sentence(),
            'type' => HolidayType::Fixed,
            'fixed_month' => $this->faker->numberBetween(1, 12),
            'fixed_day' => $this->faker->numberBetween(1, 28),
            'easter_offset_days' => null,
            'computation_rule' => null,
            'is_holy_day_of_obligation' => false,
            'is_universal' => true,
            'is_moveable' => false,
            'liturgical_color' => null,
            'team_id' => null,
        ];
    }

    /**
     * Create a fixed date holiday.
     */
    public function fixed(?int $month = null, ?int $day = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => HolidayType::Fixed,
            'fixed_month' => $month ?? $this->faker->numberBetween(1, 12),
            'fixed_day' => $day ?? $this->faker->numberBetween(1, 28),
            'easter_offset_days' => null,
            'computation_rule' => null,
            'is_moveable' => false,
        ]);
    }

    /**
     * Create an Easter-relative holiday.
     */
    public function easterRelative(?int $offsetDays = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => HolidayType::EasterRelative,
            'fixed_month' => null,
            'fixed_day' => null,
            'easter_offset_days' => $offsetDays ?? $this->faker->numberBetween(-46, 60),
            'computation_rule' => null,
            'is_moveable' => true,
        ]);
    }

    /**
     * Create a computed holiday with a rule.
     *
     * @param  array<string, mixed>|null  $rule
     */
    public function computed(?array $rule = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => HolidayType::Computed,
            'fixed_month' => null,
            'fixed_day' => null,
            'easter_offset_days' => null,
            'computation_rule' => $rule ?? ['nth' => 1, 'day' => 'sunday', 'month' => 1],
            'is_moveable' => true,
        ]);
    }

    /**
     * Mark as a holy day of obligation.
     */
    public function holyDayOfObligation(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_holy_day_of_obligation' => true,
        ]);
    }

    /**
     * Mark as team-specific (not universal).
     */
    public function teamSpecific(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_universal' => false,
        ]);
    }

    /**
     * Set the team for this holiday.
     */
    public function forTeam(int $teamId): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $teamId,
            'is_universal' => false,
        ]);
    }

    /**
     * Set liturgical color.
     */
    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'liturgical_color' => $color,
        ]);
    }

    /**
     * Create Christmas holiday.
     */
    public function christmas(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Christmas',
            'slug' => 'christmas',
            'type' => HolidayType::Fixed,
            'fixed_month' => 12,
            'fixed_day' => 25,
            'is_holy_day_of_obligation' => true,
            'is_moveable' => false,
            'liturgical_color' => 'white',
        ]);
    }

    /**
     * Create Easter Sunday holiday.
     */
    public function easter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Easter Sunday',
            'slug' => 'easter-sunday',
            'type' => HolidayType::EasterRelative,
            'fixed_month' => null,
            'fixed_day' => null,
            'easter_offset_days' => 0,
            'is_holy_day_of_obligation' => true,
            'is_moveable' => true,
            'liturgical_color' => 'white',
        ]);
    }

    /**
     * Create Ash Wednesday holiday.
     */
    public function ashWednesday(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Ash Wednesday',
            'slug' => 'ash-wednesday',
            'type' => HolidayType::EasterRelative,
            'fixed_month' => null,
            'fixed_day' => null,
            'easter_offset_days' => -46,
            'is_holy_day_of_obligation' => false,
            'is_moveable' => true,
            'liturgical_color' => 'violet',
        ]);
    }

    /**
     * Create Pentecost holiday.
     */
    public function pentecost(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Pentecost Sunday',
            'slug' => 'pentecost-sunday',
            'type' => HolidayType::EasterRelative,
            'fixed_month' => null,
            'fixed_day' => null,
            'easter_offset_days' => 49,
            'is_holy_day_of_obligation' => false,
            'is_moveable' => true,
            'liturgical_color' => 'red',
        ]);
    }

    /**
     * Create Good Friday holiday.
     */
    public function goodFriday(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Good Friday',
            'slug' => 'good-friday',
            'type' => HolidayType::EasterRelative,
            'fixed_month' => null,
            'fixed_day' => null,
            'easter_offset_days' => -2,
            'is_holy_day_of_obligation' => false,
            'is_moveable' => true,
            'liturgical_color' => 'red',
        ]);
    }
}
