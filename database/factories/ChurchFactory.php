<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
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
            'type' => $this->faker->randomElement(['parish', 'sub_parish']),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'street_address' => $this->faker->streetAddress(),
            'postal_address' => $this->faker->postcode(),
            'contact_email' => $this->faker->safeEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'parish_type' => $this->faker->word(),
            'parish_id' => Parish::factory(),
            'priest_id' => Priest::factory(),
        ];
    }
}
