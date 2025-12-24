<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\RecordsUserActions;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * SchedulePeriod does not extend BaseModel because it doesn't use the TeamScope.
 * Universal schedule periods have team_id = null and need to be globally accessible.
 * Team visibility is handled via the availableForTeam scope.
 */
class SchedulePeriod extends Model
{
    use HasFactory;
    use RecordsUserActions;
    use Searchable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
        ];
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
            'team_id' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'is_recurring' => 'boolean',
        ];
    }

    /**
     * Get the activity times associated with this schedule period.
     */
    public function activityTimes(): HasMany
    {
        return $this->hasMany(ActivityTime::class);
    }

    /**
     * Get the team that owns this schedule period.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope to get schedule periods available for a specific team.
     * Includes universal periods (null team) and team-specific periods.
     */
    public function scopeAvailableForTeam(Builder $query, ?int $teamId): Builder
    {
        return $query->where(function ($q) use ($teamId) {
            $q->whereNull('team_id');
            if ($teamId) {
                $q->orWhere('team_id', $teamId);
            }
        });
    }

    /**
     * Scope to get only active schedule periods.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get schedule periods that are currently in effect.
     */
    public function scopeCurrent(Builder $query, ?string $date = null): Builder
    {
        $date = $date ? Carbon::parse($date) : now()->toDateString();

        return $query->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    /**
     * Scope to get schedule periods that overlap with a date range.
     */
    public function scopeOverlapping(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }

    /**
     * Scope to get upcoming schedule periods.
     */
    public function scopeUpcoming(Builder $query, ?string $afterDate = null): Builder
    {
        $date = $afterDate ? Carbon::parse($afterDate) : now()->toDateString();

        return $query->where('start_date', '>', $date);
    }

    /**
     * Check if this period is currently active (in effect).
     */
    public function isCurrent(?string $date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : now()->toDateString();

        return $this->is_active
            && $this->start_date->lte($checkDate)
            && $this->end_date->gte($checkDate);
    }

    /**
     * Check if this period overlaps with another date range.
     */
    public function overlapsWithRange(string $startDate, string $endDate): bool
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return $this->start_date->lte($end) && $this->end_date->gte($start);
    }

    /**
     * Get the number of days in this period.
     */
    public function getDurationInDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Check if this is a universal schedule period (applies to all teams).
     */
    public function isUniversal(): bool
    {
        return $this->team_id === null;
    }

    /**
     * Get a formatted date range string.
     */
    public function getFormattedDateRangeAttribute(): string
    {
        return $this->start_date->format('M j, Y').' - '.$this->end_date->format('M j, Y');
    }
}
