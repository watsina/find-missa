<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\RecordsUserActions;

class BaseModel extends Model
{
    use BelongsToTeam;
    use RecordsUserActions;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
