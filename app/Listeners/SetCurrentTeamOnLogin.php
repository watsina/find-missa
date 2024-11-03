<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class SetCurrentTeamOnLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;
        // check if tht user has a current team, if not set the team for them
        if (! $user->current_team_id && $team = $user->teams()->first()) {
            $user->update(['current_team_id' => $team->id]);
        }
    }
}
