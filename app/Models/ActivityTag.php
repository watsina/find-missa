<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\RecordsUserActions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ActivityTag does not extend BaseModel because it doesn't use the TeamScope.
 * System tags have team_id = null and need to be globally accessible.
 * Team visibility is handled via the availableForTeam scope.
 */
class ActivityTag extends Model
{
    use HasFactory;
    use RecordsUserActions;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
        'is_user_visible' => 'boolean',
    ];

    /**
     * Get the team that owns this tag (if any).
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the activities that have this tag.
     */
    public function activityTimes(): BelongsToMany
    {
        return $this->belongsToMany(ActivityTime::class, 'activity_time_tag');
    }

    /**
     * Scope to get only system tags (auto-assigned).
     */
    public function scopeSystem(Builder $query): void
    {
        $query->where('is_system', true);
    }

    /**
     * Scope to get only custom tags (user-created).
     */
    public function scopeCustom(Builder $query): void
    {
        $query->where('is_system', false);
    }

    /**
     * Scope to get only user-visible tags.
     */
    public function scopeUserVisible(Builder $query): void
    {
        $query->where('is_user_visible', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeOfCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }

    /**
     * Scope to get tags available for a team (global + team-specific).
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
     * Get tags by their slugs.
     *
     * @param  array<string>  $slugs
     */
    public static function getBySlug(array $slugs): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->whereIn('slug', $slugs)
            ->get();
    }

    /**
     * Find a tag by slug.
     */
    public static function findBySlug(string $slug): ?static
    {
        return static::query()
            ->where('slug', $slug)
            ->first();
    }
}
