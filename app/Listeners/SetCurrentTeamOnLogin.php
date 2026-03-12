<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class SetCurrentTeamOnLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event)
    {
        /** @var User $user */
        $user = $event->user;
        // check if tht user has a current team, if not set the team for them
        if (! $user->current_team_id && $team = $user->allTeams()->first()) {
            return $user->switchTeam($team);
        }

        session()->put('current_team_id', $user?->current_team_id);
    }
}
