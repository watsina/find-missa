<?php

namespace App\Models;

use App\Casts\Timecast;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OneTimeActivity extends BaseModel
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
            'start_time' => Timecast::class,
            'end_time' => Timecast::class,
            'date' => 'date:Y-m-d',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OneTimeActivity $model) {
            if (! $model->activity_type_description) {
                $model->activity_type_description = $model->type;
            }
        });
        static::created(fn () => Cache::clear());
        static::updated(fn () => Cache::clear());
    }

    /**
     * Scope to get activities for a specific date.
     */
    public function scopeOnDate(Builder $query, Carbon|string $date): void
    {
        $dateString = $date instanceof Carbon ? $date->toDateString() : $date;

        $query->whereDate('date', $dateString);
    }

    /**
     * Scope to get activities for a specific church.
     */
    public function scopeForChurch(Builder $query, int|Church $church): void
    {
        $churchId = $church instanceof Church ? $church->id : $church;

        $query->where('church_id', $churchId);
    }

    /**
     * Scope to get activities of specific types.
     */
    public function scopeOfType(Builder $query, array $types): void
    {
        $query->whereIn('type', $types);
    }

    /**
     * Get the church this activity belongs to.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    /**
     * Get the team this activity belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Check if this activity occurs on a given date.
     */
    public function occursOn(Carbon $date): bool
    {
        return $this->date->isSameDay($date);
    }
}
