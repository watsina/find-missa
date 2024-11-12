<?php

namespace App\Models\Concerns;

use App\Models\Team;
use App\Models\Scopes\TeamScope;
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
        static::addGlobalScope(new TeamScope);

        static::creating(function ($model) {
            if ($teamId = session()->get('current_team_id')) {
                $model->team_id = $teamId;
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
