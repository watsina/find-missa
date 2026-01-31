<?php

namespace App\Services;

use App\Models\Church;
use App\Enums\DayPattern;
use App\Enums\ScheduleScope;
use App\Models\ActivityTime;
use App\Enums\ScheduleAction;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\ScheduleOverride;
use App\Enums\ActivityOccurrence;
use App\Models\ScheduleDayDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityTimeException;
use Illuminate\Database\Eloquent\Collection;

class ActivityTimeMigrationService
{
    /**
     * Counters for reporting stats.
     */
    protected int $countSchedules = 0;

    protected int $countOneTime = 0;

    protected int $countOverrides = 0;

    protected int $countTagSyncs = 0;

    protected int $countDeletedSchedules = 0;

    protected int $countDeletedOneTime = 0;

    protected int $countDeletedOverrides = 0;

    /**
     * @var array<int, array<string, int>>
     */
    protected array $countsByChurch = [];

    /**
     * @var array<string, array<string, int>>
     */
    protected array $countsByType = [
        'schedules' => [],
        'one_time' => [],
        'overrides' => [],
    ];

    /**
     * Migrate all churches.
     *
     * @param  bool  $force  If true, deletes existing data in new tables for the processed churches (optional/dangerous).
     */
    public function migrateAll(bool $force = false, bool $dryRun = false): array
    {
        $this->resetCounters();

        // Process churches in chunks to avoid memory issues
        Church::chunk(100, function ($churches) use ($force, $dryRun) {
            foreach ($churches as $church) {
                $this->migrateChurch($church, $force, $dryRun);
            }
        });

        return $this->getStats();
    }

    /**
     * Migrate a specific church's activity times.
     */
    public function migrateChurch(Church $church, bool $force = false, bool $dryRun = false): array
    {
        if ($dryRun) {
            if ($force) {
                $this->countExistingData($church);
            }

            return $this->previewChurch($church);
        }

        return DB::transaction(function () use ($church, $force) {
            if ($force) {
                $this->clearExistingData($church);
            }

            // Fetch all activity times for this church
            $activityTimes = ActivityTime::where('church_id', $church->id)
                ->with(['tags', 'exceptions']) // Eager load relationships
                ->get();

            if ($activityTimes->isEmpty()) {
                return $this->getStats();
            }

            // 1. Separate by Occurrence
            $oneTimeActivities = $activityTimes->where('occurrence', ActivityOccurrence::OneTime);
            $dailyActivities = $activityTimes->where('occurrence', ActivityOccurrence::Daily);
            $monthlyActivities = $activityTimes->where('occurrence', ActivityOccurrence::MonthlyNthWeekday);
            $weeklyActivities = $activityTimes->where('occurrence', ActivityOccurrence::Weekly);

            // 2. Migrate One-Time Activities
            foreach ($oneTimeActivities as $activity) {
                $this->migrateOneTimeActivity($activity);
            }

            // 3. Migrate Daily Activities
            foreach ($dailyActivities as $activity) {
                $this->migrateDailyActivity($activity);
            }

            // 4. Migrate Monthly Activities
            foreach ($monthlyActivities as $activity) {
                $this->migrateMonthlyNthActivity($activity);
            }

            // 5. Migrate Weekly Activities (with Grouping)
            $this->migrateWeeklyActivities($weeklyActivities, $church);

            // 6. Migrate Exceptions (Overrides)
            $this->migrateExceptions($activityTimes, $church);

            return $this->getStats();
        });
    }

    /**
     * Reset internal counters.
     */
    protected function resetCounters(): void
    {
        $this->countSchedules = 0;
        $this->countOneTime = 0;
        $this->countOverrides = 0;
        $this->countTagSyncs = 0;
        $this->countDeletedSchedules = 0;
        $this->countDeletedOneTime = 0;
        $this->countDeletedOverrides = 0;
        $this->countsByChurch = [];
        $this->countsByType = [
            'schedules' => [],
            'one_time' => [],
            'overrides' => [],
        ];
    }

    /**
     * Get current stats.
     */
    public function getStats(): array
    {
        return [
            'schedules_created' => $this->countSchedules,
            'one_time_created' => $this->countOneTime,
            'overrides_created' => $this->countOverrides,
            'tag_syncs' => $this->countTagSyncs,
            'deleted_schedules' => $this->countDeletedSchedules,
            'deleted_one_time' => $this->countDeletedOneTime,
            'deleted_overrides' => $this->countDeletedOverrides,
            'by_church' => $this->countsByChurch,
            'type_counts' => $this->countsByType,
        ];
    }

    /**
     * Clear existing data for a church if force is enabled.
     */
    protected function clearExistingData(Church $church): void
    {
        $this->countExistingData($church);

        OneTimeActivity::where('church_id', $church->id)->delete();
        ActivitySchedule::where('church_id', $church->id)->delete();
        ScheduleOverride::where('scope_type', ScheduleScope::Church->value)
            ->where('scope_id', $church->id)
            ->delete();
    }

    protected function countExistingData(Church $church): void
    {
        $oneTime = OneTimeActivity::where('church_id', $church->id)->count();
        $schedules = ActivitySchedule::where('church_id', $church->id)->count();
        $overrides = ScheduleOverride::where('scope_type', ScheduleScope::Church->value)
            ->where('scope_id', $church->id)
            ->count();

        $this->countDeletedOneTime += $oneTime;
        $this->countDeletedSchedules += $schedules;
        $this->countDeletedOverrides += $overrides;

        $this->recordChurchDeletions($church->id, $schedules, $oneTime, $overrides);
    }

    protected function previewChurch(Church $church): array
    {
        $activityTimes = ActivityTime::where('church_id', $church->id)
            ->with(['tags', 'exceptions'])
            ->get();

        if ($activityTimes->isEmpty()) {
            return $this->getStats();
        }

        $oneTimeActivities = $activityTimes->where('occurrence', ActivityOccurrence::OneTime);
        $dailyActivities = $activityTimes->where('occurrence', ActivityOccurrence::Daily);
        $monthlyActivities = $activityTimes->where('occurrence', ActivityOccurrence::MonthlyNthWeekday);
        $weeklyActivities = $activityTimes->where('occurrence', ActivityOccurrence::Weekly);

        foreach ($oneTimeActivities as $activity) {
            $this->previewOneTimeActivity($activity);
        }

        foreach ($dailyActivities as $activity) {
            $this->previewDailyActivity($activity);
        }

        foreach ($monthlyActivities as $activity) {
            $this->previewMonthlyNthActivity($activity);
        }

        $this->previewWeeklyActivities($weeklyActivities);
        $this->previewExceptions($activityTimes);

        return $this->getStats();
    }

    /**
     * Migrate a single One-Time Activity.
     */
    protected function migrateOneTimeActivity(ActivityTime $source): void
    {
        if (! $source->date) {
            Log::warning("Skipping OneTimeActivity ID {$source->id}: No date provided.");

            return;
        }

        $this->countOneTime++;
        $this->recordCreated($source->church_id, 'one_time', $source->type);
        $this->recordTagSyncs($source->tags->pluck('id')->all(), $source->church_id);

        $new = new OneTimeActivity;
        $new->church_id = $source->church_id;
        $new->team_id = $source->team_id; // Assuming column exists or is nullable
        $new->type = $source->type;
        $new->activity_type_description = $source->activity_type_description;
        $new->language = $source->language;
        $new->start_time = $source->start_time;
        $new->end_time = $source->end_time;
        $new->date = $source->date;
        $new->save();

        $this->syncTags($new, $source->tags->pluck('id')->all());
    }

    /**
     * Migrate a Daily Activity.
     */
    protected function migrateDailyActivity(ActivityTime $source): void
    {
        $this->countSchedules++;
        $this->recordCreated($source->church_id, 'schedules', $source->type);
        $this->recordTagSyncs($source->tags->pluck('id')->all(), $source->church_id);

        $new = new ActivitySchedule;
        $new->church_id = $source->church_id;
        $new->team_id = $source->team_id;
        $new->schedule_period_id = $source->schedule_period_id;
        $new->type = $source->type;
        $new->activity_type_description = $source->activity_type_description;
        $new->language = $source->language;
        $new->start_time = $source->start_time;
        $new->end_time = $source->end_time;
        $new->day_pattern = DayPattern::Daily;
        $new->effective_from = $source->effective_from;
        $new->effective_until = $source->effective_until;
        $new->save();

        $this->syncTags($new, $source->tags->pluck('id')->all());
    }

    /**
     * Migrate a Monthly Nth Weekday Activity.
     */
    protected function migrateMonthlyNthActivity(ActivityTime $source): void
    {
        $this->countSchedules++;
        $this->recordCreated($source->church_id, 'schedules', $source->type);
        $this->recordTagSyncs($source->tags->pluck('id')->all(), $source->church_id);

        $new = new ActivitySchedule;
        $new->church_id = $source->church_id;
        $new->team_id = $source->team_id;
        $new->schedule_period_id = $source->schedule_period_id;
        $new->type = $source->type;
        $new->activity_type_description = $source->activity_type_description;
        $new->language = $source->language;
        $new->start_time = $source->start_time;
        $new->end_time = $source->end_time;

        $new->day_pattern = DayPattern::MonthlyNth;
        $new->week_of_month = $source->week_of_month;
        $new->day_of_week = $source->day_of_week; // Ensure string format matches (e.g., "Monday")

        $new->effective_from = $source->effective_from;
        $new->effective_until = $source->effective_until;
        $new->save();

        $this->syncTags($new, $source->tags->pluck('id')->all());
    }

    /**
     * Determine the appropriate DayPattern from an array of day names.
     *
     * @param  array<string>  $days
     * @return array{pattern: DayPattern, days: array<string>|null}
     */
    protected function determineDayPattern(array $days): array
    {
        $normalizedDays = array_map(fn ($d) => ucfirst(strtolower($d)), $days);
        sort($normalizedDays);

        $weekdays = ['Friday', 'Monday', 'Thursday', 'Tuesday', 'Wednesday'];
        $allDays = ['Friday', 'Monday', 'Saturday', 'Sunday', 'Thursday', 'Tuesday', 'Wednesday'];

        if ($normalizedDays === $weekdays) {
            return ['pattern' => DayPattern::Weekdays, 'days' => null];
        }
        if ($normalizedDays === ['Saturday']) {
            return ['pattern' => DayPattern::Saturday, 'days' => null];
        }
        if ($normalizedDays === ['Sunday']) {
            return ['pattern' => DayPattern::Sunday, 'days' => null];
        }
        if ($normalizedDays === $allDays) {
            return ['pattern' => DayPattern::Daily, 'days' => null];
        }

        return ['pattern' => DayPattern::Specific, 'days' => $days];
    }

    /**
     * Split activities by day type (weekdays, saturday, sunday).
     *
     * @param  array<string, ActivityTime>  $dayActivities
     * @return array<string, array<string, ActivityTime>>
     */
    protected function splitByDayType(array $dayActivities): array
    {
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $result = [
            'weekdays' => [],
            'saturday' => [],
            'sunday' => [],
        ];

        foreach ($dayActivities as $day => $activity) {
            $normalizedDay = ucfirst(strtolower($day));
            if (in_array($normalizedDay, $weekdays)) {
                $result['weekdays'][$day] = $activity;
            } elseif ($normalizedDay === 'Saturday') {
                $result['saturday'][$day] = $activity;
            } elseif ($normalizedDay === 'Sunday') {
                $result['sunday'][$day] = $activity;
            }
        }

        return array_filter($result, fn ($group) => ! empty($group));
    }

    /**
     * Determine the default language for a schedule.
     * Prefers English, otherwise uses the most common language.
     *
     * @param  array<string, ActivityTime>  $dayActivities
     */
    protected function determineDefaultLanguage(array $dayActivities): string
    {
        $languages = collect($dayActivities)->pluck('language')->filter();

        if ($languages->contains('English')) {
            return 'English';
        }

        return $languages->countBy()->sortDesc()->keys()->first() ?? 'English';
    }

    /**
     * Create an ActivitySchedule for a specific day type group.
     *
     * @param  array<string, ActivityTime>  $dayActivities
     */
    protected function createScheduleForDayGroup(
        Church $church,
        ActivityTime $first,
        array $dayActivities
    ): void {
        $days = array_keys($dayActivities);
        $patternResult = $this->determineDayPattern($days);

        $defaultLanguage = $this->determineDefaultLanguage($dayActivities);

        $variations = [];
        foreach ($dayActivities as $day => $activity) {
            if ($activity->language && $activity->language !== $defaultLanguage) {
                $variations[$day] = $activity->language;
            }
        }

        $hasVariations = ! empty($variations);

        $this->countSchedules++;
        $this->recordCreated($church->id, 'schedules', $first->type);

        $new = new ActivitySchedule;
        $new->church_id = $church->id;
        $new->team_id = $first->team_id;
        $new->schedule_period_id = $first->schedule_period_id;
        $new->type = $first->type;
        $new->activity_type_description = $first->activity_type_description;
        $new->start_time = $first->start_time;
        $new->end_time = $first->end_time;
        $new->day_pattern = $patternResult['pattern'];
        $new->days_of_week = $patternResult['days'];
        $new->effective_from = $first->effective_from;
        $new->effective_until = $first->effective_until;
        $new->language = $defaultLanguage;
        $new->has_day_variations = $hasVariations;
        $new->save();

        if ($hasVariations) {
            foreach ($variations as $day => $language) {
                ScheduleDayDetail::create([
                    'activity_schedule_id' => $new->id,
                    'day_of_week' => $day,
                    'language' => $language,
                ]);
            }
        }

        $subGroupTagIds = collect($dayActivities)
            ->flatMap(fn ($item) => $item->tags->pluck('id'))
            ->unique()
            ->toArray();
        $this->recordTagSyncs($subGroupTagIds, $church->id);
        $this->syncTags($new, $subGroupTagIds);
    }

    /**
     * Migrate Weekly Activities with Grouping Logic.
     */
    protected function migrateWeeklyActivities(Collection $weeklyActivities, Church $church): void
    {
        // Group by key attributes EXCLUDING language to allow day variations
        $grouped = $weeklyActivities->groupBy(function ($item) {
            // Normalize times to H:i format to ensure consistent grouping
            $startTime = $item->start_time ? substr($item->start_time, 0, 5) : 'null';
            $endTime = $item->end_time ? substr($item->end_time, 0, 5) : 'null';

            return implode('|', [
                $item->schedule_period_id ?? 'null',
                $item->type,
                $startTime,
                $endTime,
                $item->effective_from?->toDateString() ?? 'null',
                $item->effective_until?->toDateString() ?? 'null',
                $item->activity_type_description ?? 'null',
                $item->team_id ?? 'null',
            ]);
        });

        foreach ($grouped as $group) {
            /** @var ActivityTime $first */
            $first = $group->first();

            // Build day → activity mapping
            $dayActivities = [];
            foreach ($group as $activity) {
                $day = $activity->day_of_week;
                if ($day) {
                    $dayActivities[$day] = $activity;
                }
            }

            if (empty($dayActivities)) {
                continue;
            }

            // Split by day type (weekdays, saturday, sunday)
            $dayTypeGroups = $this->splitByDayType($dayActivities);

            // Create a schedule for each day type sub-group
            foreach ($dayTypeGroups as $subGroupActivities) {
                $this->createScheduleForDayGroup(
                    $church,
                    $first,
                    $subGroupActivities
                );
            }
        }
    }

    /**
     * Migrate Exceptions to Schedule Overrides.
     */
    protected function migrateExceptions(Collection $activityTimes, Church $church): void
    {
        // Gather all exceptions from the source activities
        $exceptions = new Collection;
        foreach ($activityTimes as $activity) {
            if ($activity->exceptions->isNotEmpty()) {
                foreach ($activity->exceptions as $ex) {
                    $exceptions->push([
                        'exception' => $ex,
                        'parent' => $activity,
                    ]);
                }
            }
        }

        foreach ($exceptions as $item) {
            /** @var ActivityTimeException $ex */
            $ex = $item['exception'];
            /** @var ActivityTime $parent */
            $parent = $item['parent'];

            $this->countOverrides++;
            $this->recordCreated($church->id, 'overrides', $parent->type);

            $override = new ScheduleOverride;
            // Provide default name as it is required in DB schema
            $override->name = 'Migrated Exception: '.($ex->reason ?? 'Untitled');
            $override->scope_type = ScheduleScope::Church;
            $override->scope_id = $church->id;
            // Provide team_id from parent activity or fallback if needed
            $override->team_id = $parent->team_id;
            $override->action = ScheduleAction::Exclude;

            // Target the specific time/type of the parent to minimize side effects
            $override->target_times = [substr($parent->start_time, 0, 5)]; // Format H:i
            $override->activity_type = $parent->type;

            $override->start_date = $ex->start_date;
            $override->end_date = $ex->end_date;
            $override->reason = $ex->reason;
            $override->is_active = true;
            $override->save();
        }
    }

    /**
     * Copy tags from source to destination.
     */
    protected function syncTags($destination, array $tagIds): void
    {
        if (! empty($tagIds)) {
            $destination->tags()->sync($tagIds);
        }
    }

    protected function recordTagSyncs(array $tagIds, ?int $churchId = null): void
    {
        if (! empty($tagIds)) {
            $this->countTagSyncs += count($tagIds);

            if ($churchId !== null) {
                $this->countsByChurch[$churchId]['tag_syncs'] = ($this->countsByChurch[$churchId]['tag_syncs'] ?? 0) + count($tagIds);
            }
        }
    }

    protected function previewOneTimeActivity(ActivityTime $source): void
    {
        if (! $source->date) {
            Log::warning("Skipping OneTimeActivity ID {$source->id}: No date provided.");

            return;
        }

        $this->countOneTime++;
        $this->recordCreated($source->church_id, 'one_time', $source->type);
        $this->recordTagSyncs($source->tags->pluck('id')->all(), $source->church_id);
    }

    protected function previewDailyActivity(ActivityTime $source): void
    {
        $this->countSchedules++;
        $this->recordCreated($source->church_id, 'schedules', $source->type);
        $this->recordTagSyncs($source->tags->pluck('id')->all(), $source->church_id);
    }

    protected function previewMonthlyNthActivity(ActivityTime $source): void
    {
        $this->countSchedules++;
        $this->recordCreated($source->church_id, 'schedules', $source->type);
        $this->recordTagSyncs($source->tags->pluck('id')->all(), $source->church_id);
    }

    protected function previewWeeklyActivities(Collection $weeklyActivities): void
    {
        // Group by key attributes EXCLUDING language to match actual migration logic
        $grouped = $weeklyActivities->groupBy(function ($item) {
            $startTime = $item->start_time ? substr($item->start_time, 0, 5) : 'null';
            $endTime = $item->end_time ? substr($item->end_time, 0, 5) : 'null';

            return implode('|', [
                $item->schedule_period_id ?? 'null',
                $item->type,
                $startTime,
                $endTime,
                $item->effective_from?->toDateString() ?? 'null',
                $item->effective_until?->toDateString() ?? 'null',
                $item->activity_type_description ?? 'null',
                $item->team_id ?? 'null',
            ]);
        });

        foreach ($grouped as $group) {
            // Build day → activity mapping
            $dayActivities = [];
            foreach ($group as $activity) {
                $day = $activity->day_of_week;
                if ($day) {
                    $dayActivities[$day] = $activity;
                }
            }

            if (empty($dayActivities)) {
                continue;
            }

            // Split by day type (same as migrate)
            $dayTypeGroups = $this->splitByDayType($dayActivities);

            $churchId = $group->first()?->church_id;

            // Count one schedule per day type sub-group
            foreach ($dayTypeGroups as $subGroupActivities) {
                $this->countSchedules++;

                if ($churchId !== null) {
                    $this->recordCreated($churchId, 'schedules', $group->first()?->type);
                }

                $subGroupTagIds = collect($subGroupActivities)
                    ->flatMap(fn ($item) => $item->tags->pluck('id'))
                    ->unique()
                    ->toArray();
                $this->recordTagSyncs($subGroupTagIds, $churchId);
            }
        }
    }

    protected function previewExceptions(Collection $activityTimes): void
    {
        foreach ($activityTimes as $activity) {
            if ($activity->exceptions->isNotEmpty()) {
                $this->countOverrides += $activity->exceptions->count();
                $this->recordCreated($activity->church_id, 'overrides', $activity->type, $activity->exceptions->count());
            }
        }
    }

    protected function recordCreated(int $churchId, string $modelKey, ?string $type, int $increment = 1): void
    {
        $this->countsByChurch[$churchId]['schedules_created'] = $this->countsByChurch[$churchId]['schedules_created'] ?? 0;
        $this->countsByChurch[$churchId]['one_time_created'] = $this->countsByChurch[$churchId]['one_time_created'] ?? 0;
        $this->countsByChurch[$churchId]['overrides_created'] = $this->countsByChurch[$churchId]['overrides_created'] ?? 0;
        $this->countsByChurch[$churchId]['deleted_schedules'] = $this->countsByChurch[$churchId]['deleted_schedules'] ?? 0;
        $this->countsByChurch[$churchId]['deleted_one_time'] = $this->countsByChurch[$churchId]['deleted_one_time'] ?? 0;
        $this->countsByChurch[$churchId]['deleted_overrides'] = $this->countsByChurch[$churchId]['deleted_overrides'] ?? 0;
        $this->countsByChurch[$churchId]['tag_syncs'] = $this->countsByChurch[$churchId]['tag_syncs'] ?? 0;

        $key = match ($modelKey) {
            'schedules' => 'schedules_created',
            'one_time' => 'one_time_created',
            'overrides' => 'overrides_created',
            default => null,
        };

        if ($key !== null) {
            $this->countsByChurch[$churchId][$key] += $increment;
        }

        $typeKey = $type ?: 'unknown';
        $this->countsByType[$modelKey][$typeKey] = ($this->countsByType[$modelKey][$typeKey] ?? 0) + $increment;
    }

    protected function recordChurchDeletions(int $churchId, int $schedules, int $oneTime, int $overrides): void
    {
        $this->countsByChurch[$churchId]['schedules_created'] = $this->countsByChurch[$churchId]['schedules_created'] ?? 0;
        $this->countsByChurch[$churchId]['one_time_created'] = $this->countsByChurch[$churchId]['one_time_created'] ?? 0;
        $this->countsByChurch[$churchId]['overrides_created'] = $this->countsByChurch[$churchId]['overrides_created'] ?? 0;
        $this->countsByChurch[$churchId]['tag_syncs'] = $this->countsByChurch[$churchId]['tag_syncs'] ?? 0;

        $this->countsByChurch[$churchId]['deleted_schedules'] = ($this->countsByChurch[$churchId]['deleted_schedules'] ?? 0) + $schedules;
        $this->countsByChurch[$churchId]['deleted_one_time'] = ($this->countsByChurch[$churchId]['deleted_one_time'] ?? 0) + $oneTime;
        $this->countsByChurch[$churchId]['deleted_overrides'] = ($this->countsByChurch[$churchId]['deleted_overrides'] ?? 0) + $overrides;
    }
}
