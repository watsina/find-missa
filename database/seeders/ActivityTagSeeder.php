<?php

namespace Database\Seeders;

use App\Models\ActivityTag;
use Illuminate\Database\Seeder;
use App\Enums\ActivityTagCategory;

class ActivityTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = $this->getSystemTags();

        foreach ($tags as $tag) {
            ActivityTag::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }
    }

    /**
     * Get all system tags to be seeded.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getSystemTags(): array
    {
        return [
            // Time of Day tags (auto-assigned, user-visible)
            ...$this->getTimeOfDayTags(),

            // Day Type tags (auto-assigned, backoffice only)
            ...$this->getDayTypeTags(),
        ];
    }

    /**
     * Get time of day tags.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getTimeOfDayTags(): array
    {
        return [
            [
                'name' => 'Early Morning',
                'slug' => 'early-morning',
                'category' => ActivityTagCategory::TimeOfDay->value,
                'description' => 'Activities starting between 12:00 AM and 6:59 AM',
                'color' => '#1E3A5F',
                'is_system' => true,
                'is_user_visible' => true,
                'team_id' => null,
            ],
            [
                'name' => 'Morning',
                'slug' => 'morning',
                'category' => ActivityTagCategory::TimeOfDay->value,
                'description' => 'Activities starting between 7:00 AM and 11:59 AM',
                'color' => '#F59E0B',
                'is_system' => true,
                'is_user_visible' => true,
                'team_id' => null,
            ],
            [
                'name' => 'Lunch Hour',
                'slug' => 'lunch-hour',
                'category' => ActivityTagCategory::TimeOfDay->value,
                'description' => 'Activities starting between 12:00 PM and 1:59 PM',
                'color' => '#10B981',
                'is_system' => true,
                'is_user_visible' => true,
                'team_id' => null,
            ],
            [
                'name' => 'Afternoon',
                'slug' => 'afternoon',
                'category' => ActivityTagCategory::TimeOfDay->value,
                'description' => 'Activities starting between 2:00 PM and 4:59 PM',
                'color' => '#3B82F6',
                'is_system' => true,
                'is_user_visible' => true,
                'team_id' => null,
            ],
            [
                'name' => 'Evening',
                'slug' => 'evening',
                'category' => ActivityTagCategory::TimeOfDay->value,
                'description' => 'Activities starting between 5:00 PM and 7:59 PM',
                'color' => '#8B5CF6',
                'is_system' => true,
                'is_user_visible' => true,
                'team_id' => null,
            ],
            [
                'name' => 'Night',
                'slug' => 'night',
                'category' => ActivityTagCategory::TimeOfDay->value,
                'description' => 'Activities starting from 8:00 PM onwards',
                'color' => '#1F2937',
                'is_system' => true,
                'is_user_visible' => true,
                'team_id' => null,
            ],
        ];
    }

    /**
     * Get day type tags.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getDayTypeTags(): array
    {
        return [
            [
                'name' => 'Weekday',
                'slug' => 'weekday',
                'category' => ActivityTagCategory::DayType->value,
                'description' => 'Activities on Monday through Friday',
                'color' => '#6B7280',
                'is_system' => true,
                'is_user_visible' => false,
                'team_id' => null,
            ],
            [
                'name' => 'Saturday',
                'slug' => 'saturday',
                'category' => ActivityTagCategory::DayType->value,
                'description' => 'Activities on Saturday',
                'color' => '#059669',
                'is_system' => true,
                'is_user_visible' => false,
                'team_id' => null,
            ],
            [
                'name' => 'Sunday',
                'slug' => 'sunday',
                'category' => ActivityTagCategory::DayType->value,
                'description' => 'Activities on Sunday',
                'color' => '#DC2626',
                'is_system' => true,
                'is_user_visible' => false,
                'team_id' => null,
            ],
            [
                'name' => 'Daily',
                'slug' => 'daily',
                'category' => ActivityTagCategory::DayType->value,
                'description' => 'Activities that occur daily',
                'color' => '#7C3AED',
                'is_system' => true,
                'is_user_visible' => false,
                'team_id' => null,
            ],
        ];
    }
}
