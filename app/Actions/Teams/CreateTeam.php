<?php

namespace App\Actions\Teams;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CreateTeam
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  array<string, string>  $input
     */
    public function handle(Request $request): Team
    {
        Gate::forUser($request->user())->authorize('create', Team::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255', 'unique:teams'],
        ]);

        $user = $request->user();

        $user->switchTeam($team = $user->ownedTeams()->create($data));

        return $team;
    }
}
