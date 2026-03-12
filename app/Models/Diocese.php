<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use App\Models\Concerns\HasCodeName;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diocese extends BaseModel
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
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->only('name', 'code_name');
    }

    public function parishes(): HasMany
    {
        return $this->hasMany(Parish::class);
    }

    public function scheduleRules(): MorphMany
    {
        return $this->morphMany(ScheduleRule::class, 'scope');
    }

    public function scheduleOverrides(): MorphMany
    {
        return $this->morphMany(ScheduleOverride::class, 'scope');
    }
}
