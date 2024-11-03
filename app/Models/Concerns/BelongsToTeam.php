<?php

namespace App\Models\Concerns;

use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTeam
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function bootBelongsToTeam()
    {
        static::creating(function ($model) {
            if ($teamId = auth()->user()?->current_team_id) {
                $model->team_id = $teamId;
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
