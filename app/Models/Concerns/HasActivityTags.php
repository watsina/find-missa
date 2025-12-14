<?php

namespace App\Models\Concerns;

use App\Models\ActivityTag;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasActivityTags
{
    /**
     * Boot the trait.
     */
    protected static function bootHasActivityTags(): void
    {
        static::created(function ($model) {
            if (method_exists($model, 'autoAssignSystemTags')) {
                $model->autoAssignSystemTags();
            }
        });

        static::updated(function ($model) {
            if ($model->wasChanged('start_time') || $model->wasChanged('day_of_week') || $model->wasChanged('occurrence')) {
                if (method_exists($model, 'autoAssignSystemTags')) {
                    $model->autoAssignSystemTags();
                }
            }
        });
    }

    /**
     * Get the tags for this activity.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ActivityTag::class, 'activity_time_tag');
    }

    /**
     * Get only the system tags for this activity.
     */
    public function systemTags(): BelongsToMany
    {
        return $this->tags()->where('is_system', true);
    }

    /**
     * Get only the custom tags for this activity.
     */
    public function customTags(): BelongsToMany
    {
        return $this->tags()->where('is_system', false);
    }

    /**
     * Get only the user-visible tags for this activity.
     */
    public function userVisibleTags(): BelongsToMany
    {
        return $this->tags()->where('is_user_visible', true);
    }

    /**
     * Auto-assign system tags based on start_time and day_of_week.
     */
    public function autoAssignSystemTags(): void
    {
        $systemTagSlugs = $this->determineSystemTagSlugs();
        $systemTags = ActivityTag::getBySlug($systemTagSlugs);

        // Remove existing system tags and re-attach
        $this->systemTags()->detach();
        $this->tags()->attach($systemTags->pluck('id'));
    }

    /**
     * Determine which system tag slugs should be assigned based on activity properties.
     *
     * @return array<string>
     */
    protected function determineSystemTagSlugs(): array
    {
        $slugs = [];

        // Time of day tag based on start_time
        if ($this->start_time) {
            $slugs[] = $this->getTimeOfDaySlug($this->start_time);
        }

        // Day type tag based on occurrence and day_of_week
        $occurrence = $this->occurrence instanceof \App\Enums\ActivityOccurrence
            ? $this->occurrence->value
            : $this->occurrence;

        if ($occurrence === 'daily') {
            $slugs[] = 'daily';
        } elseif (in_array($occurrence, ['weekly', 'monthly-nth-weekday']) && $this->day_of_week) {
            $daySlug = $this->getDayTypeSlug($this->day_of_week);
            if ($daySlug) {
                $slugs[] = $daySlug;
            }
        }

        return array_filter($slugs);
    }

    /**
     * Get the time of day slug based on start time.
     */
    protected function getTimeOfDaySlug(string $startTime): string
    {
        // Parse the time - handle both "H:i" and "H:i:s" formats
        $time = \Carbon\Carbon::parse($startTime);
        $hour = $time->hour;

        return match (true) {
            $hour >= 0 && $hour < 7 => 'early-morning',
            $hour >= 7 && $hour < 12 => 'morning',
            $hour >= 12 && $hour < 14 => 'lunch-hour',
            $hour >= 14 && $hour < 17 => 'afternoon',
            $hour >= 17 && $hour < 20 => 'evening',
            default => 'night',
        };
    }

    /**
     * Get the day type slug based on day of week.
     */
    protected function getDayTypeSlug(string $dayOfWeek): ?string
    {
        $day = strtolower($dayOfWeek);

        return match ($day) {
            'sunday' => 'sunday',
            'saturday' => 'saturday',
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday' => 'weekday',
            default => null,
        };
    }

    /**
     * Sync custom tags (preserves system tags).
     *
     * @param  array<int>  $tagIds
     */
    public function syncCustomTags(array $tagIds): void
    {
        // Get current system tag IDs
        $systemTagIds = $this->systemTags()->pluck('activity_tags.id')->toArray();

        // Merge with provided custom tag IDs
        $allTagIds = array_unique(array_merge($systemTagIds, $tagIds));

        $this->tags()->sync($allTagIds);
    }

    /**
     * Check if the activity has a specific tag by slug.
     */
    public function hasTag(string $slug): bool
    {
        return $this->tags()->where('slug', $slug)->exists();
    }

    /**
     * Check if the activity has any of the given tags.
     *
     * @param  array<string>  $slugs
     */
    public function hasAnyTag(array $slugs): bool
    {
        return $this->tags()->whereIn('slug', $slugs)->exists();
    }

    /**
     * Check if the activity has all of the given tags.
     *
     * @param  array<string>  $slugs
     */
    public function hasAllTags(array $slugs): bool
    {
        $count = $this->tags()->whereIn('slug', $slugs)->count();

        return $count === count($slugs);
    }
}
