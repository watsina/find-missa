<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\AdorationChapel;
use App\Models\AdorationChapelException;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdorationChapelException>
 */
class AdorationChapelExceptionFactory extends Factory
{
    protected $model = AdorationChapelException::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');

        return [
            'adoration_chapel_id' => AdorationChapel::factory(),
            'team_id' => Team::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $startDate->format('Y-m-d'),
            'reason' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Set exception dates.
     */
    public function dates(string $start, string $end): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }

    /**
     * Set a single day exception.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $date,
            'end_date' => $date,
        ]);
    }

    /**
     * Set the reason for the exception.
     */
    public function withReason(string $reason): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => $reason,
        ]);
    }
}
