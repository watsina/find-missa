<?php

namespace Database\Factories;

use App\Models\Team;
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
        // Default to weekly occurrence to avoid requiring extra fields
        return [
            'type' => 'mass',
            'activity_type_description' => 'mass',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'language' => 'English',
            'church_id' => Church::factory(),
            'occurrence' => 'weekly',
            'date' => null,
            'day_of_week' => 'Sunday',
            'team_id' => Team::factory(),
            'week_of_month' => null,
            'bi_weekly_start_date' => null,
        ];
    }

    public function daily($type = 'mass')
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_of_week' => null,
            'occurrence' => 'daily',
            'date' => null,
            'week_of_month' => null,
            'bi_weekly_start_date' => null,
        ]);
    }

    public function oneTime($date, $type = 'mass')
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_of_week' => null,
            'occurrence' => 'one-time',
            'date' => $date,
            'week_of_month' => null,
            'bi_weekly_start_date' => null,
        ]);
    }

    public function weekly($dayOfWeek = 'Sunday', $type = 'mass')
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_of_week' => $dayOfWeek,
            'occurrence' => 'weekly',
            'date' => null,
            'week_of_month' => null,
            'bi_weekly_start_date' => null,
        ]);
    }

    /**
     * Create a bi-weekly activity (every other week).
     */
    public function biWeekly($dayOfWeek = 'Wednesday', $startDate = null, $type = 'mass')
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_of_week' => $dayOfWeek,
            'occurrence' => 'bi-weekly',
            'date' => null,
            'week_of_month' => null,
            'bi_weekly_start_date' => $startDate ?? now()->toDateString(),
        ]);
    }

    /**
     * Create a monthly nth weekday activity (e.g., first Friday of the month).
     */
    public function monthlyNthWeekday($dayOfWeek = 'Friday', $weekOfMonth = ActivityTime::WEEK_FIRST, $type = 'mass')
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'day_of_week' => $dayOfWeek,
            'occurrence' => 'monthly-nth-weekday',
            'date' => null,
            'week_of_month' => $weekOfMonth,
            'bi_weekly_start_date' => null,
        ]);
    }
}
