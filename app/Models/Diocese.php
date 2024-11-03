<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diocese extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];
}
