<?php

namespace Database\Factories;

use App\Models\ActivityTime;
use App\Models\ActivityTimeException;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityTimeExceptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ActivityTimeException::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'activity_time_id' => ActivityTime::factory(),
            'team_id' => ActivityTime::factory(),
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'reason' => $this->faker->words(3, true),
        ];
    }
}
