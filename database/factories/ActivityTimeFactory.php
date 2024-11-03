<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Church;
use App\Enums\ActivityType;
use App\Models\ActivityTime;
use App\Enums\ActivityOccurrence;
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
            'type' => $this->faker->randomElement(ActivityType::values()),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'language' => $this->faker->languageCode,
            'church_id' => Church::factory(),
            'occurrence' => $this->faker->randomElement(ActivityOccurrence::values()),
            'date' => $this->faker->date,
            'day_of_week' => $this->faker->dayOfWeek(),
            'team_id' => Team::factory(),
        ];
    }
}
