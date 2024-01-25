<?php

namespace Database\Factories;

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
            'district' => $this->faker->word(),
            'meta' => '{}',
        ];
    }
}
