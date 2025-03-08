<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use App\Models\Concerns\HasCodeName;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubParish extends BaseModel
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
        'parish_id' => 'integer',
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

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function churches(): MorphMany
    {
        return $this->morphMany(Church::class, 'parishable');
    }
}
