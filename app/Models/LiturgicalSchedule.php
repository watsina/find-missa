<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiturgicalSchedule extends BaseModel
{
    use HasFactory;

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
            'holiday_id' => 'integer',
            'year' => 'integer',
            'replaces_regular_schedule' => 'boolean',
        ];
    }

    /**
     * Scope to get schedules for a specific church.
     */
    public function scopeForChurch(Builder $query, int|Church $church): void
    {
        $churchId = $church instanceof Church ? $church->id : $church;

        $query->where('church_id', $churchId);
    }

    /**
     * Scope to get schedules for a specific holiday.
     */
    public function scopeForHoliday(Builder $query, int|Holiday $holiday): void
    {
        $holidayId = $holiday instanceof Holiday ? $holiday->id : $holiday;

        $query->where('holiday_id', $holidayId);
    }

    /**
     * Scope to get schedules for a specific year or recurring (year is null).
     */
    public function scopeForYear(Builder $query, ?int $year): void
    {
        $query->where(function (Builder $q) use ($year) {
            $q->whereNull('year');
            if ($year) {
                $q->orWhere('year', $year);
            }
        });
    }

    /**
     * Scope to get schedules that apply on a specific date.
     * This checks if the holiday occurs on this date for the given year.
     */
    public function scopeApplicableOnDate(Builder $query, Carbon $date): void
    {
        $query->whereHas('holiday.instances', function (Builder $q) use ($date) {
            $q->whereDate('date', $date->toDateString());
        })->forYear($date->year);
    }

    /**
     * Get the church this schedule belongs to.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    /**
     * Get the team this schedule belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the holiday this schedule is for.
     */
    public function holiday(): BelongsTo
    {
        return $this->belongsTo(Holiday::class);
    }

    /**
     * Get the times for this liturgical schedule.
     */
    public function times(): HasMany
    {
        return $this->hasMany(LiturgicalScheduleTime::class);
    }

    /**
     * Check if this schedule applies to a specific year.
     */
    public function appliesToYear(int $year): bool
    {
        return $this->year === null || $this->year === $year;
    }

    /**
     * Check if this schedule replaces regular schedules.
     */
    public function replacesRegularSchedule(): bool
    {
        return $this->replaces_regular_schedule;
    }
}
