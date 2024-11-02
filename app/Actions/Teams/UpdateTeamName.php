<?php

namespace App\Actions\Teams;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class UpdateTeamName
{
    /**
     * Validate and update the given team's name.
     *
     * @param  array<string, string>  $input
     */
    public function handle(Request $request, Team $team): Team
    {
        Gate::forUser($request->user())->authorize('update', $team);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('teams')->ignore($team)],
        ]);

        $team->update($data);

        return $team;
    }
}
