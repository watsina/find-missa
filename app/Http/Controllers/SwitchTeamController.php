<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class SwitchTeamController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $team = Team::findOrFail($request->team_id);

        if (! $request->user()->switchTeam($team)) {
            abort(404);
        }

        return response(200);
    }
}
