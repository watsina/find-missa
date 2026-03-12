<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\GlobalActivityTimeException;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GlobalActivityTimeException>
 */
class GlobalActivityTimeExceptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reason' => $this->faker->words(3, true),
            'message' => $this->faker->words(5, true),
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'team_id' => Team::factory(),
        ];
    }
}
