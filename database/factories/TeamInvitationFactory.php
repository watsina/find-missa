<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamInvitation>
 */
class TeamInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'email' => $this->faker->email,
            'team_id' => Team::factory(),
            'role_id' => Role::factory(),
        ];
    }
}
