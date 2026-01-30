<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use App\Models\Concerns\HasCodeName;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Church extends BaseModel
{
    use HasCodeName;
    use HasFactory;
    use Searchable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'priest_id' => 'integer',
        'latitude' => 'decimal:4',
        'longitude' => 'decimal:4',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->only('name', 'city', 'district');
    }

    /**
     * Get the diocese_id through the parishable relationship.
     */
    protected function dioceseId(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->parishable instanceof Parish) {
                    return $this->parishable->diocese_id;
                }

                if ($this->parishable instanceof SubParish) {
                    return $this->parishable->parish?->diocese_id;
                }

                return null;
            }
        );
    }

    /**
     * Get the parent parishable model (parish or sub-parish).
     */
    public function parishable(): MorphTo
    {
        return $this->morphTo();
    }

    public function priest(): BelongsTo
    {
        return $this->belongsTo(Priest::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityTime::class);
    }

    public function activitySchedules(): HasMany
    {
        return $this->hasMany(ActivitySchedule::class);
    }

    public function liturgicalSchedules(): HasMany
    {
        return $this->hasMany(LiturgicalSchedule::class);
    }

    public function oneTimeActivities(): HasMany
    {
        return $this->hasMany(OneTimeActivity::class);
    }

    public function scheduleRules(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ScheduleRule::class, 'scope');
    }

    public function scheduleOverrides(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ScheduleOverride::class, 'scope');
    }
}
