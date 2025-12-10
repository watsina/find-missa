<?php

namespace App\Models;

use App\Casts\Timecast;
use Laravel\Scout\Searchable;
use Illuminate\Support\Carbon;
use App\Models\Concerns\HasActivityTags;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityTime extends BaseModel
{
    use HasActivityTags;
    use HasFactory;
    use Searchable;

    protected static function booted()
    {
        static::creating(function ($model) {
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
    protected function casts()
    {
        return [
            'id' => 'integer',
            'church_id' => 'integer',
            'schedule_period_id' => 'integer',
            'date' => 'date:Y-m-d',
            'effective_from' => 'date:Y-m-d',
            'effective_until' => 'date:Y-m-d',
            'start_time' => Timecast::class,
            'end_time' => Timecast::class,
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->only('start_time', 'occurrence', 'day_of_week', 'type');
    }

    /**
     * Scope a query to only include users of a given type.
     */
    public function scopeOfType(Builder $query, array $types): void
    {
        $query->whereIn('type', $types);
    }

    /**
     * Scope a query to only include activity times effective on a given date.
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
     * Scope a query to only include activity times for a specific schedule period.
     */
    public function scopeForSchedulePeriod(Builder $query, int|SchedulePeriod $schedulePeriod): void
    {
        $schedulePeriodId = $schedulePeriod instanceof SchedulePeriod
            ? $schedulePeriod->id
            : $schedulePeriod;

        $query->where('schedule_period_id', $schedulePeriodId);
    }

    /**
     * Scope a query to only include activity times without a schedule period (default/permanent times).
     */
    public function scopeWithoutSchedulePeriod(Builder $query): void
    {
        $query->whereNull('schedule_period_id');
    }

    /**
     * Check if this activity time is currently effective.
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

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function schedulePeriod(): BelongsTo
    {
        return $this->belongsTo(SchedulePeriod::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ActivityTimeException::class);
    }
}
