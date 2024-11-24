<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Parish;
use App\Models\SubParish;
use Faker\Provider\en_US\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubParishFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubParish::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'parish_id' => Parish::factory(),
            'city' => $this->faker->city,
            'district' => Address::state(),
            'contact_email' => $this->faker->safeEmail(),
            'website_url' => $this->faker->url(),
            'office_address' => $this->faker->word(),
            'team_id' => Team::factory(),
        ];
    }
}
