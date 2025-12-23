<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Enums\ActivityTagCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityTag>
 */
class ActivityTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'category' => ActivityTagCategory::Custom->value,
            'description' => $this->faker->optional()->sentence(),
            'color' => $this->faker->optional()->hexColor(),
            'is_system' => false,
            'is_user_visible' => true,
            'team_id' => null,
        ];
    }

    /**
     * Indicate that the tag is a system tag.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    /**
     * Indicate that the tag is not user visible.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_user_visible' => false,
        ]);
    }

    /**
     * Set the tag category.
     */
    public function category(ActivityTagCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category->value,
        ]);
    }

    /**
     * Set the team for this tag.
     */
    public function forTeam(int $teamId): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $teamId,
        ]);
    }
}
