<?php

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HolidayInstance>
 */
class HolidayInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = $this->faker->numberBetween(2020, 2030);

        return [
            'holiday_id' => Holiday::factory(),
            'year' => $year,
            'date' => $this->faker->dateTimeBetween("$year-01-01", "$year-12-31")->format('Y-m-d'),
            'display_name' => null,
            'notes' => null,
        ];
    }

    /**
     * Set the year for this instance.
     */
    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
        ]);
    }

    /**
     * Set the date for this instance.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
            'year' => (int) substr($date, 0, 4),
        ]);
    }

    /**
     * Set a custom display name.
     */
    public function withDisplayName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'display_name' => $name,
        ]);
    }
}
