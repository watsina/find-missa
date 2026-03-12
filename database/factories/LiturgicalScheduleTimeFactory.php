<?php

namespace Database\Factories;

use App\Models\LiturgicalSchedule;
use App\Models\LiturgicalScheduleTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LiturgicalScheduleTime>
 */
class LiturgicalScheduleTimeFactory extends Factory
{
    protected $model = LiturgicalScheduleTime::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'liturgical_schedule_id' => LiturgicalSchedule::factory(),
            'type' => 'mass',
            'activity_type_description' => null,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'language' => 'English',
        ];
    }

    /**
     * Set the start time.
     */
    public function startingAt(string $time): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $time,
        ]);
    }

    /**
     * Set the language.
     */
    public function inLanguage(string $language): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => $language,
        ]);
    }

    /**
     * Set the activity type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'activity_type_description' => $type,
        ]);
    }

    /**
     * Set a custom description.
     */
    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type_description' => $description,
        ]);
    }
}
