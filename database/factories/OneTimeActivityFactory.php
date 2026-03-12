<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Church;
use App\Models\OneTimeActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OneTimeActivity>
 */
class OneTimeActivityFactory extends Factory
{
    protected $model = OneTimeActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'team_id' => Team::factory(),
            'type' => 'mass',
            'activity_type_description' => 'mass',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'language' => 'English',
            'date' => now()->toDateString(),
            'reason' => null,
        ];
    }

    /**
     * Set the date for the activity.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
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
     * Set reason.
     */
    public function withReason(string $reason): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => $reason,
        ]);
    }
}
