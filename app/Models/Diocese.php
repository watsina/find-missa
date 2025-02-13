<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\Concerns\HasCodeName;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diocese extends BaseModel
{
    use HasCodeName;
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->code_name = Str::kebab($model->name.' '.$model->type);
        });
    }

    public function parishes(): HasMany
    {
        return $this->hasMany(Parish::class);
    }
}
