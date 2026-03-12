<?php

namespace Database\Factories;

use App\Models\Ability;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ability>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::lower($this->faker->unique()->word),
            'description' => $this->faker->sentence(3, true),
        ];
    }
}
