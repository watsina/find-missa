<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Church;
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
            'type' => 'mass',
            'activity_type_description' => 'mass',
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

    public function daily($type)
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_of_week' => null,
            'occurrence' => 'daily',
            'date' => null,
        ]);
    }

    public function oneTime($date, $type)
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_of_week' => null,
            'occurrence' => 'one-time',
            'date' => $date,
        ]);
    }

    public function weekly($dayOfWeek = 'Sunday', $type = 'mass')
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_of_week' => $dayOfWeek,
            'occurrence' => 'weekly',
            'date' => null,
        ]);
    }
}
