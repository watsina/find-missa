<?php

namespace Database\Factories;

use App\Models\Diocese;
use Illuminate\Database\Eloquent\Factories\Factory;

class DioceseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Diocese::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'type' => $this->faker->randomElement(['diocese', 'archdiocese']),
            'office_address' => $this->faker->word(),
            'postal_address' => $this->faker->word(),
            'country' => $this->faker->country(),
        ];
    }
}
