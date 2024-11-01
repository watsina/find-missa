<?php

namespace Database\Factories;

use App\Models\Parish;
use App\Models\SubParish;
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
            'city' => $this->faker->word(),
            'office_address' => $this->faker->word(),
        ];
    }
}
