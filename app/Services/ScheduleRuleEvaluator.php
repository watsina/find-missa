<?php

namespace App\Services;

use App\Models\Church;
use App\Models\ScheduleRule;
use App\Enums\ScheduleAction;
use Illuminate\Support\Carbon;
use App\Models\ScheduleOverride;
use Illuminate\Support\Collection;
use App\Enums\ScheduleRuleCondition;
use App\DataTransferObjects\ComputedActivity;

/**
 * Evaluates schedule rules and overrides against activities.
 *
 * This service determines which rules and overrides apply to activities
 * on a specific date and applies the appropriate actions (exclude/modify).
 */
class ScheduleRuleEvaluator
{
    public function __construct(
        protected HolidayService $holidayService,
    ) {}

    /**
     * Evaluate and apply rules and overrides to a collection of activities.
     *
     * @param  Collection<int, ComputedActivity>  $activities
     * @param  Collection<int, ScheduleRule>  $rules
     * @param  Collection<int, ScheduleOverride>  $overrides
     * @return Collection<int, ComputedActivity>
     */
    public function evaluateAndApply(
        Collection $activities,
        Collection $rules,
        Collection $overrides,
        Carbon $date,
        ?int $teamId = null,
    ): Collection {
        // First, determine which conditions are active for this date
        $activeConditions = $this->getActiveConditionsForDate($date, $teamId);

        // Filter rules to only those with active conditions
        $applicableRules = $rules->filter(
            fn (ScheduleRule $rule) => $this->ruleConditionIsActive($rule, $activeConditions)
        );

        // Apply rules to activities
        $activities = $this->applyRules($activities, $applicableRules, $date);

        // Apply overrides to remaining activities
        $activities = $this->applyOverrides($activities, $overrides, $date);

        return $activities;
    }

    /**
     * Get active conditions for a date (holiday info).
     *
     * @return array{is_public_holiday: bool, holidays: array<int, array{slug: string, name: string}>}
     */
    public function getActiveConditionsForDate(Carbon $date, ?int $teamId = null): array
    {
        $holidaysOnDate = $this->holidayService->getHolidaysOnDate($date->toDateString(), $teamId);

        return [
            'is_public_holiday' => $holidaysOnDate->isNotEmpty(),
            'holidays' => $holidaysOnDate->map(fn ($instance) => [
                'slug' => $instance->holiday->slug,
                'name' => $instance->holiday->name,
            ])->values()->all(),
        ];
    }

    /**
     * Check if a rule's condition is active based on the date's conditions.
     *
     * @param  array{is_public_holiday: bool, holidays: array<int, array{slug: string, name: string}>}  $activeConditions
     */
    protected function ruleConditionIsActive(ScheduleRule $rule, array $activeConditions): bool
    {
        if (! $rule->is_active) {
            return false;
        }

        return match ($rule->condition_type) {
            ScheduleRuleCondition::PublicHoliday => $activeConditions['is_public_holiday'],
            ScheduleRuleCondition::LiturgicalDay => $this->matchesLiturgicalDay($rule, $activeConditions),
        };
    }

    /**
     * Check if the rule matches any liturgical day in the active conditions.
     *
     * @param  array{is_public_holiday: bool, holidays: array<int, array{slug: string, name: string}>}  $activeConditions
     */
    protected function matchesLiturgicalDay(ScheduleRule $rule, array $activeConditions): bool
    {
        if (empty($activeConditions['holidays'])) {
            return false;
        }

        // If no specific holidays are defined, match any liturgical day
        if (empty($rule->condition_holidays)) {
            return true;
        }

        // Check if any of the date's holidays match the rule's condition holidays
        foreach ($activeConditions['holidays'] as $holiday) {
            if (in_array($holiday['slug'], $rule->condition_holidays, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply rules to the activities collection.
     *
     * @param  Collection<int, ComputedActivity>  $activities
     * @param  Collection<int, ScheduleRule>  $rules
     * @return Collection<int, ComputedActivity>
     */
    protected function applyRules(Collection $activities, Collection $rules, Carbon $date): Collection
    {
        // Sort rules by priority (lower number = higher priority)
        $sortedRules = $rules->sortBy('priority');

        return $activities->filter(function (ComputedActivity $activity) use ($sortedRules) {
            foreach ($sortedRules as $rule) {
                if ($this->ruleMatchesActivity($rule, $activity)) {
                    if ($rule->action === ScheduleAction::Exclude) {
                        return false; // Remove the activity
                    }
                }
            }

            return true;
        })->map(function (ComputedActivity $activity) use ($sortedRules) {
            // Handle modify actions
            foreach ($sortedRules as $rule) {
                if ($this->ruleMatchesActivity($rule, $activity)) {
                    if ($rule->action === ScheduleAction::Modify) {
                        // Currently, rules only support exclude action
                        // Future enhancement: add time modification support
                    }
                }
            }

            return $activity;
        })->values();
    }

    /**
     * Check if a rule matches an activity.
     */
    protected function ruleMatchesActivity(ScheduleRule $rule, ComputedActivity $activity): bool
    {
        // Check activity type
        if (! $rule->matchesActivityType($activity->type)) {
            return false;
        }

        // Check time
        if (! $rule->matchesTime($activity->startTime)) {
            return false;
        }

        // Note: Day pattern matching is done at query level, not activity level
        // since ComputedActivity doesn't store the original day pattern

        return true;
    }

    /**
     * Apply overrides to the activities collection.
     *
     * @param  Collection<int, ComputedActivity>  $activities
     * @param  Collection<int, ScheduleOverride>  $overrides
     * @return Collection<int, ComputedActivity>
     */
    protected function applyOverrides(Collection $activities, Collection $overrides, Carbon $date): Collection
    {
        // Filter to only overrides that cover this date
        $applicableOverrides = $overrides->filter(
            fn (ScheduleOverride $override) => $override->is_active && $override->coversDate($date)
        );

        if ($applicableOverrides->isEmpty()) {
            return $activities;
        }

        return $activities->filter(function (ComputedActivity $activity) use ($applicableOverrides) {
            foreach ($applicableOverrides as $override) {
                if ($this->overrideMatchesActivity($override, $activity)) {
                    if ($override->action === ScheduleAction::Exclude) {
                        return false; // Remove the activity
                    }
                }
            }

            return true;
        })->map(function (ComputedActivity $activity) use ($applicableOverrides) {
            // Handle modify actions
            foreach ($applicableOverrides as $override) {
                if ($this->overrideMatchesActivity($override, $activity)) {
                    if ($override->action === ScheduleAction::Modify) {
                        $replacementTime = $override->getReplacementTime($activity->startTime);
                        if ($replacementTime) {
                            return $activity->withStartTime($replacementTime);
                        }
                    }
                }
            }

            return $activity;
        })->values();
    }

    /**
     * Check if an override matches an activity.
     */
    protected function overrideMatchesActivity(ScheduleOverride $override, ComputedActivity $activity): bool
    {
        // Check activity type
        if (! $override->matchesActivityType($activity->type)) {
            return false;
        }

        // Check time
        if (! $override->matchesTime($activity->startTime)) {
            return false;
        }

        return true;
    }

    /**
     * Get applicable rules for a church.
     *
     * @return Collection<int, ScheduleRule>
     */
    public function getRulesForChurch(Church $church, ?array $activityTypes = null): Collection
    {
        // Ensure parishable is loaded for diocese_id accessor
        if (! $church->relationLoaded('parishable')) {
            $church->load('parishable');
        }

        $query = ScheduleRule::query()
            ->active()
            ->applicableToChurch($church, $church->diocese_id)
            ->orderedByPriority();

        if ($activityTypes) {
            $query->where(function ($q) use ($activityTypes) {
                $q->whereNull('activity_type')
                    ->orWhereIn('activity_type', $activityTypes);
            });
        }

        return $query->get();
    }

    /**
     * Get applicable overrides for a church on a date.
     *
     * @return Collection<int, ScheduleOverride>
     */
    public function getOverridesForChurch(Church $church, Carbon $date, ?array $activityTypes = null): Collection
    {
        // Ensure parishable is loaded for diocese_id accessor
        if (! $church->relationLoaded('parishable')) {
            $church->load('parishable');
        }

        $query = ScheduleOverride::query()
            ->active()
            ->applicableToChurch($church, $church->diocese_id)
            ->coversDate($date);

        if ($activityTypes) {
            $query->where(function ($q) use ($activityTypes) {
                $q->whereNull('activity_type')
                    ->orWhereIn('activity_type', $activityTypes);
            });
        }

        return $query->get();
    }
}
