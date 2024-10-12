<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\ActivityTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityTimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ActivityTime::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'start_time' => $this->faker->time,
            'end_time' => $this->faker->time,
            'language' => $this->faker->languageCode,
            'church_id' => Church::factory(),
            'occurence' => $this->faker->randomElement(['weekly', 'one-time', 'monthly']),
            'date' => $this->faker->dateTime(),
            'day_of_week' => $this->faker->dayOfWeek(),
        ];
    }
}
