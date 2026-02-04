<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Church;
use App\Models\AdorationChapel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdorationChapel>
 */
class AdorationChapelFactory extends Factory
{
    protected $model = AdorationChapel::class;

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
            'name' => null,
            'notes' => null,
        ];
    }

    /**
     * Set a name for the chapel.
     */
    public function named(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * Add notes to the chapel.
     */
    public function withNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $notes,
        ]);
    }
}
