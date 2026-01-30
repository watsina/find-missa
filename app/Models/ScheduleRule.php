<?php

namespace App\Models;

use App\Enums\ScheduleScope;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleRuleCondition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleRule extends BaseModel
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
            'condition_type' => ScheduleRuleCondition::class,
            'action' => ScheduleAction::class,
            'target_times' => 'array',
            'target_day_patterns' => 'array',
            'condition_holidays' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    /**
     * Scope to get only active rules.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to get rules for a specific church.
     */
    public function scopeForChurch(Builder $query, int|Church $church): void
    {
        $churchId = $church instanceof Church ? $church->id : $church;

        $query->where('scope_type', ScheduleScope::Church->value)
            ->where('scope_id', $churchId);
    }

    /**
     * Scope to get rules for a specific diocese.
     */
    public function scopeForDiocese(Builder $query, int|Diocese $diocese): void
    {
        $dioceseId = $diocese instanceof Diocese ? $diocese->id : $diocese;

        $query->where('scope_type', ScheduleScope::Diocese->value)
            ->where('scope_id', $dioceseId);
    }

    /**
     * Scope to get rules applicable to a church (church-level + diocese-level).
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
     * Scope to get rules with a specific condition type.
     */
    public function scopeWithCondition(Builder $query, ScheduleRuleCondition $condition): void
    {
        $query->where('condition_type', $condition->value);
    }

    /**
     * Scope to get rules ordered by priority.
     */
    public function scopeOrderedByPriority(Builder $query): void
    {
        $query->orderBy('priority', 'asc');
    }

    /**
     * Get the owning scope model (Church or Diocese).
     */
    public function scope(): MorphTo
    {
        return $this->morphTo('scope', 'scope_type', 'scope_id');
    }

    /**
     * Get the team this rule belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Check if a time matches this rule's target times.
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
     * Check if a day pattern matches this rule's target day patterns.
     */
    public function matchesDayPattern(string $dayPattern): bool
    {
        if (empty($this->target_day_patterns)) {
            return true;
        }

        return in_array($dayPattern, $this->target_day_patterns);
    }

    /**
     * Check if an activity type matches this rule.
     */
    public function matchesActivityType(string $activityType): bool
    {
        if (empty($this->activity_type)) {
            return true;
        }

        return $this->activity_type === $activityType;
    }

    /**
     * Check if a holiday slug matches this rule's condition holidays.
     */
    public function matchesHoliday(string $holidaySlug): bool
    {
        if ($this->condition_type !== ScheduleRuleCondition::LiturgicalDay) {
            return false;
        }

        if (empty($this->condition_holidays)) {
            return true;
        }

        return in_array($holidaySlug, $this->condition_holidays);
    }
}
