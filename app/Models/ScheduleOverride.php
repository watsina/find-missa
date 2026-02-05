<?php

namespace App\Models;

use App\Enums\ScheduleScope;
use App\Enums\ScheduleAction;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleOverride extends BaseModel
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
            'scope_id' => 'integer',
            'team_id' => 'integer',
            'scope_type' => ScheduleScope::class,
            'action' => ScheduleAction::class,
            'target_times' => 'array',
            'target_day_patterns' => 'array',
            'replacement_times' => 'array',
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to get only active overrides.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to get overrides that cover a specific date.
     */
    public function scopeCoversDate(Builder $query, Carbon|string $date): void
    {
        $dateString = $date instanceof Carbon ? $date->toDateString() : $date;

        $query->whereDate('start_date', '<=', $dateString)
            ->whereDate('end_date', '>=', $dateString);
    }

    /**
     * Scope to get overrides for a specific church.
     */
    public function scopeForChurch(Builder $query, int|Church $church): void
    {
        $churchId = $church instanceof Church ? $church->id : $church;

        $query->where('scope_type', ScheduleScope::Church->value)
            ->where('scope_id', $churchId);
    }

    /**
     * Scope to get overrides for a specific diocese.
     */
    public function scopeForDiocese(Builder $query, int|Diocese $diocese): void
    {
        $dioceseId = $diocese instanceof Diocese ? $diocese->id : $diocese;

        $query->where('scope_type', ScheduleScope::Diocese->value)
            ->where('scope_id', $dioceseId);
    }

    /**
     * Scope to get overrides applicable to a church (church-level + diocese-level).
     */
    public function scopeApplicableToChurch(Builder $query, Church $church, ?int $dioceseId = null): void
    {
        $query->where(function (Builder $q) use ($church, $dioceseId) {
            $q->where(function (Builder $inner) use ($church) {
                $inner->where('scope_type', ScheduleScope::Church->value)
                    ->where('scope_id', $church->id);
            });

            if ($dioceseId) {
                $q->orWhere(function (Builder $inner) use ($dioceseId) {
                    $inner->where('scope_type', ScheduleScope::Diocese->value)
                        ->where('scope_id', $dioceseId);
                });
            }
        });
    }

    /**
     * Get the owning scope model (Church or Diocese).
     */
    public function scope(): MorphTo
    {
        return $this->morphTo('scope', 'scope_type', 'scope_id');
    }

    /**
     * Get the team this override belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Check if a time matches this override's target times.
     */
    public function matchesTime(string $time): bool
    {
        if (empty($this->target_times)) {
            return true;
        }

        $normalizedTime = substr($time, 0, 5);

        return in_array($normalizedTime, $this->target_times);
    }

    /**
     * Check if a day pattern matches this override's target day patterns.
     */
    public function matchesDayPattern(string $dayPattern): bool
    {
        if (empty($this->target_day_patterns)) {
            return true;
        }

        return in_array($dayPattern, $this->target_day_patterns);
    }

    /**
     * Check if an activity type matches this override.
     */
    public function matchesActivityType(string $activityType): bool
    {
        if (empty($this->activity_type)) {
            return true;
        }

        return $this->activity_type === $activityType;
    }

    /**
     * Get the replacement time for an original time, if this is a modify action.
     */
    public function getReplacementTime(string $originalTime): ?string
    {
        if ($this->action !== ScheduleAction::Modify || empty($this->replacement_times)) {
            return null;
        }

        $normalizedTime = substr($originalTime, 0, 5);

        foreach ($this->replacement_times as $replacement) {
            if (($replacement['original'] ?? null) === $normalizedTime) {
                return $replacement['replacement'] ?? null;
            }
        }

        return null;
    }

    /**
     * Check if this override covers a specific date.
     */
    public function coversDate(Carbon $date): bool
    {
        return $date->between($this->start_date, $this->end_date);
    }
}
