<?php

namespace App\Models;

use App\Casts\Timecast;
use App\Enums\DayPattern;
use Laravel\Scout\Searchable;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActivitySchedule extends BaseModel
{
    use HasFactory;
    use Searchable;

    /**
     * Week of month constants.
     */
    public const WEEK_FIRST = 1;

    public const WEEK_SECOND = 2;

    public const WEEK_THIRD = 3;

    public const WEEK_FOURTH = 4;

    public const WEEK_LAST = 5;

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

    protected static function booted(): void
    {
        static::creating(function (ActivitySchedule $model) {
            if (! $model->activity_type_description) {
                $model->activity_type_description = $model->type;
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'church_id' => 'integer',
            'team_id' => 'integer',
            'schedule_period_id' => 'integer',
            'start_time' => Timecast::class,
            'end_time' => Timecast::class,
            'day_pattern' => DayPattern::class,
            'days_of_week' => 'array',
            'week_of_month' => 'integer',
            'has_day_variations' => 'boolean',
            'effective_from' => 'date:Y-m-d',
            'effective_until' => 'date:Y-m-d',
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return $this->only('start_time', 'day_pattern', 'type', 'language');
    }

    /**
     * Scope a query to only include schedules of given types.
     */
    public function scopeOfType(Builder $query, array $types): void
    {
        $query->whereIn('type', $types);
    }

    /**
     * Scope a query to only include schedules effective on a given date.
     */
    public function scopeEffectiveOn(Builder $query, Carbon|string|null $date = null): void
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date ?? 'today');
        $dateString = $date->toDateString();

        $query->where(function (Builder $q) use ($dateString) {
            $q->where(function (Builder $inner) {
                $inner->whereNull('effective_from')
                    ->whereNull('effective_until');
            })
                ->orWhere(function (Builder $inner) use ($dateString) {
                    $inner->whereNotNull('effective_from')
                        ->whereNull('effective_until')
                        ->whereDate('effective_from', '<=', $dateString);
                })
                ->orWhere(function (Builder $inner) use ($dateString) {
                    $inner->whereNull('effective_from')
                        ->whereNotNull('effective_until')
                        ->whereDate('effective_until', '>=', $dateString);
                })
                ->orWhere(function (Builder $inner) use ($dateString) {
                    $inner->whereNotNull('effective_from')
                        ->whereNotNull('effective_until')
                        ->whereDate('effective_from', '<=', $dateString)
                        ->whereDate('effective_until', '>=', $dateString);
                });
        });
    }

    /**
     * Scope a query to only include schedules that match the given date's day pattern.
     */
    public function scopeMatchesDayPattern(Builder $query, Carbon $date): void
    {
        $dayOfWeek = $date->dayName;
        $isWeekday = in_array($dayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
        $isSaturday = $dayOfWeek === 'Saturday';
        $isSunday = $dayOfWeek === 'Sunday';

        $query->where(function (Builder $q) use ($dayOfWeek, $isWeekday, $isSaturday, $isSunday, $date) {
            $q->where('day_pattern', DayPattern::Daily->value)
                ->when($isWeekday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Weekdays->value))
                ->when($isSaturday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Saturday->value))
                ->when($isSunday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Sunday->value))
                ->orWhere(function (Builder $inner) use ($dayOfWeek) {
                    $inner->where('day_pattern', DayPattern::Specific->value)
                        ->whereJsonContains('days_of_week', $dayOfWeek);
                })
                ->orWhere(function (Builder $inner) use ($dayOfWeek, $date) {
                    $weekNumber = (int) ceil($date->day / 7);
                    $isLastOccurrence = $date->copy()->addWeek()->month !== $date->month;

                    $inner->where('day_pattern', DayPattern::MonthlyNth->value)
                        ->where('day_of_week', $dayOfWeek)
                        ->where(function (Builder $weekQuery) use ($weekNumber, $isLastOccurrence) {
                            $weekQuery->where('week_of_month', $weekNumber);
                            if ($isLastOccurrence) {
                                $weekQuery->orWhere('week_of_month', self::WEEK_LAST);
                            }
                        });
                });
        });
    }

    /**
     * Scope a query to only include schedules for a specific schedule period.
     */
    public function scopeForSchedulePeriod(Builder $query, int|SchedulePeriod $schedulePeriod): void
    {
        $schedulePeriodId = $schedulePeriod instanceof SchedulePeriod
            ? $schedulePeriod->id
            : $schedulePeriod;

        $query->where('schedule_period_id', $schedulePeriodId);
    }

    /**
     * Scope a query to only include schedules without a schedule period.
     */
    public function scopeWithoutSchedulePeriod(Builder $query): void
    {
        $query->whereNull('schedule_period_id');
    }

    /**
     * Check if this schedule is currently effective.
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
     * Check if this schedule occurs on a given date based on its day pattern.
     */
    public function occursOn(Carbon $date): bool
    {
        $dayPattern = $this->day_pattern instanceof DayPattern
            ? $this->day_pattern
            : DayPattern::tryFrom($this->day_pattern);

        $dayOfWeek = $date->dayName;

        return match ($dayPattern) {
            DayPattern::Daily => true,
            DayPattern::Weekdays => in_array($dayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
            DayPattern::Saturday => $dayOfWeek === 'Saturday',
            DayPattern::Sunday => $dayOfWeek === 'Sunday',
            DayPattern::Specific => in_array($dayOfWeek, $this->days_of_week ?? []),
            DayPattern::MonthlyNth => $this->occursOnMonthlyNth($date),
            default => false,
        };
    }

    /**
     * Check if this schedule occurs on a given date for monthly nth pattern.
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

        if ($this->week_of_month === self::WEEK_LAST) {
            $nextWeek = $date->copy()->addWeek();

            return $nextWeek->month !== $date->month;
        }

        return $weekNumber === $this->week_of_month;
    }

    /**
     * Get the resolved values for a specific day, considering day details.
     *
     * @return array{start_time: string, end_time: string|null, language: string, activity_type_description: string|null, is_skipped: bool}
     */
    public function getResolvedValuesForDay(string $dayOfWeek): array
    {
        $dayDetail = $this->dayDetails->firstWhere('day_of_week', strtolower($dayOfWeek))
            ?? $this->dayDetails->firstWhere('day_of_week', $dayOfWeek);

        return [
            'start_time' => $dayDetail?->start_time ?? $this->start_time,
            'end_time' => $dayDetail?->end_time ?? $this->end_time,
            'language' => $dayDetail?->language ?? $this->language,
            'activity_type_description' => $dayDetail?->activity_type_description ?? $this->activity_type_description,
            'is_skipped' => $dayDetail?->is_skipped ?? false,
        ];
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

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function schedulePeriod(): BelongsTo
    {
        return $this->belongsTo(SchedulePeriod::class);
    }

    public function dayDetails(): HasMany
    {
        return $this->hasMany(ScheduleDayDetail::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ActivityTag::class, 'activity_schedule_tag');
    }
}
