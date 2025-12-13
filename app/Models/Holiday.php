<?php

namespace App\Models;

use App\Enums\HolidayType;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\RecordsUserActions;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Holiday does not extend BaseModel because it doesn't use the TeamScope.
 * Universal holidays have team_id = null and need to be globally accessible.
 * Team visibility is handled via the availableForTeam scope.
 */
class Holiday extends Model
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
            'liturgical_color' => $this->liturgical_color,
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
            'type' => HolidayType::class,
            'fixed_month' => 'integer',
            'fixed_day' => 'integer',
            'easter_offset_days' => 'integer',
            'computation_rule' => 'array',
            'is_holy_day_of_obligation' => 'boolean',
            'is_universal' => 'boolean',
            'is_moveable' => 'boolean',
        ];
    }

    /**
     * Get the team that owns this holiday (if any).
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get computed instances of this holiday.
     */
    public function instances(): HasMany
    {
        return $this->hasMany(HolidayInstance::class);
    }

    /**
     * Scope to get only universal holidays.
     */
    public function scopeUniversal(Builder $query): void
    {
        $query->where('is_universal', true);
    }

    /**
     * Scope to get only team-specific holidays.
     */
    public function scopeTeamSpecific(Builder $query): void
    {
        $query->where('is_universal', false);
    }

    /**
     * Scope to get holidays that are holy days of obligation.
     */
    public function scopeHolyDaysOfObligation(Builder $query): void
    {
        $query->where('is_holy_day_of_obligation', true);
    }

    /**
     * Scope to get holidays of a specific type.
     */
    public function scopeOfType(Builder $query, HolidayType $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Scope to get holidays available for a team (universal + team-specific).
     */
    public function scopeAvailableForTeam(Builder $query, ?int $teamId): void
    {
        $query->where(function (Builder $q) use ($teamId) {
            $q->whereNull('team_id');
            if ($teamId) {
                $q->orWhere('team_id', $teamId);
            }
        });
    }

    /**
     * Scope to get moveable feasts (Easter-based).
     */
    public function scopeMoveable(Builder $query): void
    {
        $query->where('is_moveable', true);
    }

    /**
     * Scope to get fixed date holidays.
     */
    public function scopeFixed(Builder $query): void
    {
        $query->where('type', HolidayType::Fixed);
    }

    /**
     * Find a holiday by slug.
     */
    public static function findBySlug(string $slug): ?static
    {
        return static::query()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Check if this is a fixed date holiday.
     */
    public function isFixed(): bool
    {
        return $this->type === HolidayType::Fixed;
    }

    /**
     * Check if this is an Easter-relative holiday.
     */
    public function isEasterRelative(): bool
    {
        return $this->type === HolidayType::EasterRelative;
    }

    /**
     * Check if this is a computed holiday.
     */
    public function isComputed(): bool
    {
        return $this->type === HolidayType::Computed;
    }

    /**
     * Get the instance for a specific year.
     */
    public function getInstanceForYear(int $year): ?HolidayInstance
    {
        return $this->instances()
            ->where('year', $year)
            ->first();
    }

    /**
     * Check if an instance exists for a specific year.
     */
    public function hasInstanceForYear(int $year): bool
    {
        return $this->instances()
            ->where('year', $year)
            ->exists();
    }
}
