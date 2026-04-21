<?php

namespace App\Models\Concerns;

use App\Models\SchedulePeriod;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared schedule logic for ActivityTime (v1) and ActivitySchedule (v2).
 *
 * Provides week-of-month constants, effective date scoping, schedule period
 * scoping, and monthly-nth occurrence checking.
 */
trait HasScheduleLogic
{
    /**
     * Week of month constants.
     */
    public const WEEK_FIRST = 1;

    public const WEEK_SECOND = 2;

    public const WEEK_THIRD = 3;

    public const WEEK_FOURTH = 4;

    public const WEEK_LAST = 5;

    /**
     * Boot the trait — auto-set activity_type_description on creation.
     */
    protected static function bootHasScheduleLogic(): void
    {
        static::creating(function ($model) {
            if (! $model->activity_type_description) {
                $model->activity_type_description = $model->type;
            }
        });
    }

    /**
     * Get the week of month options for UI.
     *
     * @return array<int, string>
     */
    public static function weekOfMonthOptions(): array
    {
        return [
            self::WEEK_FIRST => 'First',
            self::WEEK_SECOND => 'Second',
            self::WEEK_THIRD => 'Third',
            self::WEEK_FOURTH => 'Fourth',
            self::WEEK_LAST => 'Last',
        ];
    }

    /**
     * Scope a query to only include records of given types.
     */
    public function scopeOfType(Builder $query, array $types): void
    {
        $query->whereIn('type', $types);
    }

    /**
     * Scope a query to only include records effective on a given date.
     */
    public function scopeEffectiveOn(Builder $query, Carbon|string|null $date = null): void
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date ?? 'today');
        $dateString = $date->toDateString();

        $query->where(function (Builder $q) use ($dateString) {
            // No effective dates set (always effective)
            $q->where(function (Builder $inner) {
                $inner->whereNull('effective_from')
                    ->whereNull('effective_until');
            })
                // Has start date but no end date, and we're after start
                ->orWhere(function (Builder $inner) use ($dateString) {
                    $inner->whereNotNull('effective_from')
                        ->whereNull('effective_until')
                        ->whereDate('effective_from', '<=', $dateString);
                })
                // Has end date but no start date, and we're before end
                ->orWhere(function (Builder $inner) use ($dateString) {
                    $inner->whereNull('effective_from')
                        ->whereNotNull('effective_until')
                        ->whereDate('effective_until', '>=', $dateString);
                })
                // Has both dates and we're within range
                ->orWhere(function (Builder $inner) use ($dateString) {
                    $inner->whereNotNull('effective_from')
                        ->whereNotNull('effective_until')
                        ->whereDate('effective_from', '<=', $dateString)
                        ->whereDate('effective_until', '>=', $dateString);
                });
        });
    }

    /**
     * Scope a query to only include records for a specific schedule period.
     */
    public function scopeForSchedulePeriod(Builder $query, int|SchedulePeriod $schedulePeriod): void
    {
        $schedulePeriodId = $schedulePeriod instanceof SchedulePeriod
            ? $schedulePeriod->id
            : $schedulePeriod;

        $query->where('schedule_period_id', $schedulePeriodId);
    }

    /**
     * Scope a query to only include records without a schedule period (default/permanent).
     */
    public function scopeWithoutSchedulePeriod(Builder $query): void
    {
        $query->whereNull('schedule_period_id');
    }

    /**
     * Check if this record is currently effective based on its effective date range.
     */
    public function isEffective(?Carbon $date = null): bool
    {
        $date = $date ?? Carbon::today();

        if (is_null($this->effective_from) && is_null($this->effective_until)) {
            return true;
        }

        if (! is_null($this->effective_from) && $this->effective_from->gt($date)) {
            return false;
        }

        if (! is_null($this->effective_until) && $this->effective_until->lt($date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if this record occurs on a given date for a monthly nth weekday pattern.
     *
     * Expects the model to have `week_of_month` and `day_of_week` attributes.
     */
    protected function occursOnMonthlyNth(Carbon $date): bool
    {
        if (! $this->week_of_month || ! $this->day_of_week) {
            return false;
        }

        if ($date->dayName !== $this->day_of_week) {
            return false;
        }

        $weekNumber = (int) ceil($date->day / 7);

        // Handle "last" week (value 5)
        if ($this->week_of_month === self::WEEK_LAST) {
            $nextWeek = $date->copy()->addWeek();

            return $nextWeek->month !== $date->month;
        }

        return $weekNumber === $this->week_of_month;
    }

    /**
     * Get the human-readable label for the week of month.
     */
    public function getWeekOfMonthLabelAttribute(): ?string
    {
        if (! $this->week_of_month) {
            return null;
        }

        return self::weekOfMonthOptions()[$this->week_of_month] ?? null;
    }
}
