<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Parish;
use App\Models\SubParish;
use Illuminate\Support\Carbon;
use App\Models\AdorationChapel;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\LiturgicalSchedule;
use Illuminate\Support\Collection;
use App\DataTransferObjects\ComputedActivity;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Main service for computing available activities on a specific date.
 *
 * This service orchestrates the computation of all activities by:
 * 1. Getting matching activity schedules for the date's day pattern
 * 2. Applying day details to resolve actual values
 * 3. Checking and applying schedule rules (public holidays, liturgical days)
 * 4. Checking and applying schedule overrides for the date
 * 5. Checking for liturgical schedules that might replace regular schedules
 * 6. Adding one-time activities for the date
 * 7. Returning combined, filtered results
 */
class ActivityScheduleService
{
    public function __construct(
        protected ScheduleRuleEvaluator $ruleEvaluator,
        protected HolidayService $holidayService,
    ) {}

    /**
     * Get all computed activities for a church on a specific date.
     *
     * @param  array<int, string>|null  $activityTypes  Filter by activity types (e.g., ['mass', 'confession'])
     * @param  bool  $includeChurch  Whether to include church data in the result
     * @return Collection<int, ComputedActivity>
     */
    public function getActivitiesForChurchOnDate(
        Church $church,
        Carbon $date,
        ?array $activityTypes = null,
        bool $includeChurch = false,
    ): Collection {
        $teamId = $church->team_id;

        // Check if a suppressing holiday falls on this date. When it does,
        // regular schedules, rules, and overrides are all bypassed — only
        // liturgical schedules and one-time activities are returned.
        $suppressingHolidays = $this->holidayService->getSuppressingHolidaysOnDate(
            $date->toDateString(),
            $teamId
        );

        if ($suppressingHolidays->isNotEmpty()) {
            return $this->getActivitiesForSuppressingHoliday(
                $church, $date, $activityTypes, $includeChurch
            );
        }

        // Check for liturgical schedules that replace regular schedules
        $liturgicalActivities = $this->getLiturgicalActivities($church, $date, $activityTypes, $includeChurch);
        $liturgicalReplacesRegular = $this->liturgicalReplacesRegular($church, $date, $activityTypes);

        // Get regular schedule activities (unless completely replaced by liturgical)
        $scheduleActivities = collect();
        if (! $liturgicalReplacesRegular) {
            $scheduleActivities = $this->getScheduleActivities($church, $date, $activityTypes, $includeChurch);
        }

        // Get one-time activities
        $oneTimeActivities = $this->getOneTimeActivities($church, $date, $activityTypes, $includeChurch);

        // Combine all activities
        $allActivities = collect()
            ->concat($scheduleActivities)
            ->concat($liturgicalActivities)
            ->concat($oneTimeActivities);

        // Get rules and overrides for the church
        $rules = $this->ruleEvaluator->getRulesForChurch($church, $activityTypes);
        $overrides = $this->ruleEvaluator->getOverridesForChurch($church, $date, $activityTypes);

        // Apply rules and overrides
        $filteredActivities = $this->ruleEvaluator->evaluateAndApply(
            $allActivities,
            $rules,
            $overrides,
            $date,
            $teamId
        );

        // Deduplicate and sort by time
        return $this->deduplicateAndSort($filteredActivities);
    }

    /**
     * Get activities for a church on a date where a suppressing holiday applies.
     *
     * On suppressing holidays (e.g., Good Friday), regular schedules are not
     * shown. Only liturgical schedules (if the church has defined them for
     * this holiday) and one-time activities are returned. Schedule rules and
     * overrides are also bypassed since there are no regular schedules to act on.
     *
     * @param  array<int, string>|null  $activityTypes
     * @return Collection<int, ComputedActivity>
     */
    protected function getActivitiesForSuppressingHoliday(
        Church $church,
        Carbon $date,
        ?array $activityTypes = null,
        bool $includeChurch = false,
    ): Collection {
        $liturgicalActivities = $this->getLiturgicalActivities($church, $date, $activityTypes, $includeChurch);
        $oneTimeActivities = $this->getOneTimeActivities($church, $date, $activityTypes, $includeChurch);

        return $this->deduplicateAndSort(
            $liturgicalActivities->concat($oneTimeActivities)
        );
    }

    /**
     * Get all computed activities for multiple churches on a specific date.
     *
     * @param  Collection<int, Church>|array<int, Church>  $churches
     * @param  array<int, string>|null  $activityTypes
     * @return Collection<int, ComputedActivity>
     */
    public function getActivitiesForChurchesOnDate(
        Collection|array $churches,
        Carbon $date,
        ?array $activityTypes = null,
    ): Collection {
        $churches = collect($churches);

        return $churches->flatMap(
            fn (Church $church) => $this->getActivitiesForChurchOnDate($church, $date, $activityTypes)
        );
    }

    /**
     * Get activities from regular schedules.
     *
     * @return Collection<int, ComputedActivity>
     */
    protected function getScheduleActivities(Church $church, Carbon $date, ?array $activityTypes, bool $includeChurch = false): Collection
    {
        $query = ActivitySchedule::query()
            ->where('church_id', $church->id)
            ->effectiveOn($date)
            ->matchesDayPattern($date)
            ->with(['dayDetails']);

        if ($includeChurch) {
            $query->with([
                'church' => function ($query) {
                    $query->with([
                        'parishable' => function (MorphTo $morphTo) {
                            $morphTo->morphWith([
                                Parish::class => ['diocese'],
                                SubParish::class => ['parish.diocese'],
                            ]);
                        },
                    ]);
                },
            ]);
        }

        if ($activityTypes) {
            $query->ofType($activityTypes);
        }

        $schedules = $query->get();

        return $schedules->map(function (ActivitySchedule $schedule) use ($date, $church, $includeChurch) {
            $dayOfWeek = $date->dayName;
            $resolvedValues = $schedule->getResolvedValuesForDay($dayOfWeek);

            // Skip if this day is marked as skipped
            if ($resolvedValues['is_skipped']) {
                return null;
            }

            $activity = ComputedActivity::fromSchedule($schedule, $date, $resolvedValues);

            // Attach church if needed and not already loaded
            if ($includeChurch && $activity->church === null) {
                $activity = $activity->withChurch($church);
            }

            return $activity;
        })->filter()->values();
    }

    /**
     * Get activities from liturgical schedules.
     *
     * @return Collection<int, ComputedActivity>
     */
    protected function getLiturgicalActivities(Church $church, Carbon $date, ?array $activityTypes, bool $includeChurch = false): Collection
    {
        $query = LiturgicalSchedule::query()
            ->forChurch($church)
            ->applicableOnDate($date)
            ->with(['times', 'holiday']);

        if ($includeChurch) {
            $query->with([
                'church' => function ($query) {
                    $query->with([
                        'parishable' => function (MorphTo $morphTo) {
                            $morphTo->morphWith([
                                Parish::class => ['diocese'],
                                SubParish::class => ['parish.diocese'],
                            ]);
                        },
                    ]);
                },
            ]);
        }

        $liturgicalSchedules = $query->get();

        return $liturgicalSchedules->flatMap(function (LiturgicalSchedule $schedule) use ($date, $activityTypes, $church, $includeChurch) {
            $times = $schedule->times;

            // Filter by activity type if specified
            if ($activityTypes) {
                $times = $times->filter(fn ($time) => in_array($time->type, $activityTypes, true));
            }

            return $times->map(function ($time) use ($schedule, $date, $church, $includeChurch) {
                $activity = ComputedActivity::fromLiturgicalTime($time, $schedule, $date);

                // Attach church if needed and not already loaded
                if ($includeChurch && $activity->church === null) {
                    $activity = $activity->withChurch($church);
                }

                return $activity;
            });
        })->values();
    }

    /**
     * Check if liturgical schedules replace regular schedules for a date.
     */
    protected function liturgicalReplacesRegular(Church $church, Carbon $date, ?array $activityTypes): bool
    {
        $query = LiturgicalSchedule::query()
            ->forChurch($church)
            ->applicableOnDate($date)
            ->where('replaces_regular_schedule', true);

        // If filtering by activity types, only check liturgical schedules
        // that have times of those types
        if ($activityTypes) {
            $query->whereHas('times', function ($q) use ($activityTypes) {
                $q->whereIn('type', $activityTypes);
            });
        }

        return $query->exists();
    }

    /**
     * Get one-time activities for a date.
     *
     * @return Collection<int, ComputedActivity>
     */
    protected function getOneTimeActivities(Church $church, Carbon $date, ?array $activityTypes, bool $includeChurch = false): Collection
    {
        $query = OneTimeActivity::query()
            ->forChurch($church)
            ->onDate($date);

        if ($includeChurch) {
            $query->with([
                'church' => function ($query) {
                    $query->with([
                        'parishable' => function (MorphTo $morphTo) {
                            $morphTo->morphWith([
                                Parish::class => ['diocese'],
                                SubParish::class => ['parish.diocese'],
                            ]);
                        },
                    ]);
                },
            ]);
        }

        if ($activityTypes) {
            $query->ofType($activityTypes);
        }

        return $query->get()->map(function (OneTimeActivity $activity) use ($church, $includeChurch) {
            $computedActivity = ComputedActivity::fromOneTimeActivity($activity);

            // Attach church if needed and not already loaded
            if ($includeChurch && $computedActivity->church === null) {
                $computedActivity = $computedActivity->withChurch($church);
            }

            return $computedActivity;
        })->values();
    }

    /**
     * Deduplicate activities by unique key and sort by time.
     *
     * @param  Collection<int, ComputedActivity>  $activities
     * @return Collection<int, ComputedActivity>
     */
    protected function deduplicateAndSort(Collection $activities): Collection
    {
        return $activities
            ->keyBy(fn (ComputedActivity $activity) => $activity->getUniqueKey())
            ->values()
            ->sortBy(fn (ComputedActivity $activity) => $activity->startTime)
            ->values();
    }

    /**
     * Get activities for a date range.
     *
     * @param  array<int, string>|null  $activityTypes
     * @return Collection<string, Collection<int, ComputedActivity>> Keyed by date string
     */
    public function getActivitiesForChurchInRange(
        Church $church,
        Carbon $startDate,
        Carbon $endDate,
        ?array $activityTypes = null,
    ): Collection {
        $activities = collect();
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->toDateString();
            $activities[$dateKey] = $this->getActivitiesForChurchOnDate(
                $church,
                $currentDate->copy(),
                $activityTypes
            );
            $currentDate->addDay();
        }

        return $activities;
    }

    /**
     * Check if a church has any activities on a specific date.
     */
    public function churchHasActivitiesOnDate(
        Church $church,
        Carbon $date,
        ?array $activityTypes = null,
    ): bool {
        return $this->getActivitiesForChurchOnDate($church, $date, $activityTypes)->isNotEmpty();
    }

    /**
     * Get the next date with activities for a church.
     */
    public function getNextDateWithActivities(
        Church $church,
        Carbon $fromDate,
        ?array $activityTypes = null,
        int $maxDaysToSearch = 30,
    ): ?Carbon {
        $currentDate = $fromDate->copy();
        $endDate = $fromDate->copy()->addDays($maxDaysToSearch);

        while ($currentDate->lte($endDate)) {
            if ($this->churchHasActivitiesOnDate($church, $currentDate, $activityTypes)) {
                return $currentDate;
            }
            $currentDate->addDay();
        }

        return null;
    }

    /**
     * Get holiday information for a date.
     *
     * @return array{is_public_holiday: bool, holidays: array<int, array{slug: string, name: string}>}
     */
    public function getHolidayInfoForDate(Carbon $date, ?int $teamId = null): array
    {
        return $this->ruleEvaluator->getActiveConditionsForDate($date, $teamId);
    }

    /**
     * Search for activities across churches with filters.
     *
     * This is the main method for the public search functionality.
     *
     * @param  array<int, string>  $activityTypes  Activity types to search for (e.g., ['mass', 'confession'])
     * @param  string|null  $churchName  Filter by church name (partial match)
     * @param  string|null  $district  Filter by district or city (partial match)
     * @param  string|null  $startTime  Only include activities at or after this time (H:i format)
     * @param  int  $limit  Maximum number of results to return
     * @return Collection<int, ComputedActivity>
     */
    public function searchActivities(
        Carbon $date,
        array $activityTypes,
        ?string $churchName = null,
        ?string $district = null,
        ?string $startTime = null,
        int $limit = 50,
    ): Collection {
        // First, get all churches that match the filters
        $churchQuery = Church::query();

        if ($churchName) {
            $churchQuery->whereLikeInsensitive('name', $churchName);
        }

        if ($district) {
            $churchQuery->where(function ($q) use ($district) {
                $q->whereLikeInsensitive('district', $district)
                    ->orWhereLikeInsensitive('city', $district);
            });
        }

        $churches = $churchQuery
            ->with([
                'parishable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Parish::class => ['diocese'],
                        SubParish::class => ['parish.diocese'],
                    ]);
                },
            ])
            ->get();

        // Get activities for each church
        $allActivities = $churches->flatMap(
            fn (Church $church) => $this->getActivitiesForChurchOnDate(
                $church,
                $date,
                $activityTypes,
                includeChurch: true
            )
        );

        // If searching for adoration, also include open adoration chapels
        if (in_array('adoration', $activityTypes, true)) {
            $chapelActivities = $this->getOpenAdorationChapels(
                $date,
                $churchName,
                $district,
            );
            $allActivities = $allActivities->concat($chapelActivities);
        }

        // Filter by start time if specified
        if ($startTime) {
            $allActivities = $allActivities->filter(
                function (ComputedActivity $activity) use ($startTime) {
                    // For confession and adoration, show activities still ongoing at search time
                    if (in_array($activity->type, ['confession', 'adoration'], true)) {
                        return $activity->endTime === null || $activity->endTime > $startTime;
                    }

                    // For mass and other types, only show activities starting at or after search time
                    return $activity->startTime >= $startTime;
                }
            );
        }

        // Sort by start time and limit
        return $allActivities
            ->sortBy(fn (ComputedActivity $activity) => $activity->startTime)
            ->values()
            ->take($limit);
    }

    /**
     * Get adoration chapels that have a schedule for the given date.
     *
     * Returns adoration chapels that are not closed on the given date,
     * filtered by optional church name and district criteria.
     * Time-based filtering (e.g. excluding chapels that have already closed)
     * is handled by searchActivities().
     *
     * @return Collection<int, ComputedActivity>
     */
    public function getOpenAdorationChapels(
        Carbon $date,
        ?string $churchName = null,
        ?string $district = null,
    ): Collection {
        // Build the query for chapels with their churches
        $query = AdorationChapel::query()
            ->with([
                'church' => function ($query) {
                    $query->with([
                        'parishable' => function (MorphTo $morphTo) {
                            $morphTo->morphWith([
                                Parish::class => ['diocese'],
                                SubParish::class => ['parish.diocese'],
                            ]);
                        },
                    ]);
                },
                'schedules.dayDetails',
                'exceptions',
            ]);

        // Filter by church criteria
        $query->whereHas('church', function ($churchQuery) use ($churchName, $district) {
            if ($churchName) {
                $churchQuery->whereLikeInsensitive('name', $churchName);
            }
            if ($district) {
                $churchQuery->where(function ($q) use ($district) {
                    $q->whereLikeInsensitive('district', $district)
                        ->orWhereLikeInsensitive('city', $district);
                });
            }
        });

        $chapels = $query->get();

        // Convert each open chapel to a ComputedActivity
        // Time-based filtering is handled by searchActivities() which checks
        // if the chapel is still open (endTime > startTime) for adoration types.
        return $chapels
            ->map(function (AdorationChapel $chapel) use ($date) {
                // Get hours for this date
                $hours = $chapel->getHoursForDate($date);

                // Skip if no schedule or closed
                if (! $hours || $hours['is_closed']) {
                    return null;
                }

                return ComputedActivity::fromAdorationChapel(
                    $chapel,
                    $date,
                    $hours,
                    $chapel->church
                );
            })
            ->filter()
            ->values();
    }
}
