<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Parish;
use App\Models\Diocese;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParishFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Parish::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'diocese_id' => Diocese::factory(),
            'office_address' => $this->faker->word(),
            'contact_email' => $this->faker->safeEmail(),
            'website_url' => $this->faker->url(),
            'city' => $this->faker->city,
            'team_id' => Team::factory(),
        ];
    }
}
