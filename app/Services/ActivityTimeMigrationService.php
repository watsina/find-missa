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

    /**
     * Migrate all churches.
     *
     * @param  bool  $force  If true, deletes existing data in new tables for the processed churches (optional/dangerous).
     */
    public function migrateAll(bool $force = false): array
    {
        $this->resetCounters();

        // Process churches in chunks to avoid memory issues
        Church::chunk(100, function ($churches) use ($force) {
            foreach ($churches as $church) {
                $this->migrateChurch($church, $force);
            }
        });

        return $this->getStats();
    }

    /**
     * Migrate a specific church's activity times.
     */
    public function migrateChurch(Church $church, bool $force = false): array
    {
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
        ];
    }

    /**
     * Clear existing data for a church if force is enabled.
     */
    protected function clearExistingData(Church $church): void
    {
        OneTimeActivity::where('church_id', $church->id)->delete();
        ActivitySchedule::where('church_id', $church->id)->delete();
        ScheduleOverride::where('scope_type', ScheduleScope::Church->value)
            ->where('scope_id', $church->id)
            ->delete();
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

        $this->copyTags($source, $new);
        $this->countOneTime++;
    }

    /**
     * Migrate a Daily Activity.
     */
    protected function migrateDailyActivity(ActivityTime $source): void
    {
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

        $this->copyTags($source, $new);
        $this->countSchedules++;
    }

    /**
     * Migrate a Monthly Nth Weekday Activity.
     */
    protected function migrateMonthlyNthActivity(ActivityTime $source): void
    {
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

        $this->copyTags($source, $new);
        $this->countSchedules++;
    }

    /**
     * Migrate Weekly Activities with Grouping Logic.
     */
    protected function migrateWeeklyActivities(Collection $weeklyActivities, Church $church): void
    {
        // Group by key attributes to merge same-time activities into one schedule
        $grouped = $weeklyActivities->groupBy(function ($item) {
            // Normalize times to H:i format to ensure consistent grouping
            $startTime = $item->start_time ? substr($item->start_time, 0, 5) : 'null';
            $endTime = $item->end_time ? substr($item->end_time, 0, 5) : 'null';

            return implode('|', [
                $item->schedule_period_id ?? 'null',
                $item->type,
                $item->language,
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

            // Extract distinct days
            $days = $group->pluck('day_of_week')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // If no days found (shouldn't happen for Weekly), skip or handle
            if (empty($days)) {
                continue;
            }

            $new = new ActivitySchedule;
            $new->church_id = $church->id;
            $new->team_id = $first->team_id;
            $new->schedule_period_id = $first->schedule_period_id;
            $new->type = $first->type;
            $new->activity_type_description = $first->activity_type_description;
            $new->language = $first->language;
            $new->start_time = $first->start_time;
            $new->end_time = $first->end_time;

            // Determine Pattern: if all 7 days, convert to Daily? Or keep as Specific?
            // For now, mapping 'Weekly' -> 'Specific' is safest to preserve intent unless it's genuinely every day.
            // If the set of days is exactly M-F, we could use Weekdays, but Specific is robust.
            $new->day_pattern = DayPattern::Specific;
            $new->days_of_week = $days;

            $new->effective_from = $first->effective_from;
            $new->effective_until = $first->effective_until;
            $new->save();

            // Merge tags from all items in the group
            $allTagIds = $group->flatMap(fn ($item) => $item->tags->pluck('id'))->unique()->toArray();
            if (! empty($allTagIds)) {
                $new->tags()->sync($allTagIds);
            }

            $this->countSchedules++;
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

            $this->countOverrides++;
        }
    }

    /**
     * Copy tags from source to destination.
     */
    protected function copyTags(ActivityTime $source, $destination): void
    {
        if ($source->tags->isNotEmpty()) {
            // attach() works if destination model has BelongsToMany 'tags'
            $destination->tags()->sync($source->tags->pluck('id'));
        }
    }
}
