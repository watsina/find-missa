<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use App\Models\Concerns\HasCodeName;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parish extends BaseModel
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
        'diocese_id' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->code_name = Str::kebab($model->name.' '.$model->city);
        });
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->only('name');
    }

    public function subParishes(): HasMany
    {
        return $this->hasMany(SubParish::class);
    }

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class);
    }

    public function churches(): MorphMany
    {
        return $this->morphMany(Church::class, 'parishable');
    }

    public function priests(): HasMany
    {
        return $this->hasMany(Priest::class);
    }
}
