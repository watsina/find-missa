<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Church;
use App\Models\Parish;
use Faker\Provider\en_US\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChurchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Church::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'type' => 'parish',
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'street_address' => $this->faker->streetAddress(),
            'postal_address' => $this->faker->postcode(),
            'contact_email' => $this->faker->safeEmail(),
            'website_url' => $this->faker->url(),
            'google_maps_link' => $this->faker->url(),
            'city' => $this->faker->city,
            'district' => Address::state(),
            'contact_phone' => $this->faker->phoneNumber(),
            'parishable_id' => Parish::factory(),
            'parishable_type' => Parish::class,
            'team_id' => Team::factory(),
        ];
    }
}
