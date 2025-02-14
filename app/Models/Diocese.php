<?php

namespace App\Models;

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

    public function parishes(): HasMany
    {
        return $this->hasMany(Parish::class);
    }
}
