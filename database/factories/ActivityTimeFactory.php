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
            'start_time' => $this->faker->regexify('[A-Za-z0-9]{100}'),
            'end_time' => $this->faker->regexify('[A-Za-z0-9]{100}'),
            'language' => $this->faker->regexify('[A-Za-z0-9]{100}'),
            'church_id' => Church::factory(),
            'occurence' => $this->faker->regexify('[A-Za-z0-9]{100}'),
            'date' => $this->faker->dateTime(),
            'day_of_week' => $this->faker->regexify('[A-Za-z0-9]{100}'),
        ];
    }
}
