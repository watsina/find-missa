<?php

namespace Database\Factories;

use App\Models\Parish;
use App\Models\Priest;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Priest::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'parish_id' => Parish::factory(),
        ];
    }
}
