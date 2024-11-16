<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Actions\Teams\CreateTeam;
use App\Http\Resources\TeamResource;
use Illuminate\Support\Facades\Gate;
use App\Actions\Teams\UpdateTeamName;
use App\Http\Resources\TeamCollection;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Team::class);

        $teams = Team::all();

        return new TeamCollection($teams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTeam $createTeam, Request $request)
    {
        Gate::authorize('create', Team::class);

        $team = $createTeam->handle($request);

        return new TeamResource($team);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        Gate::authorize('view', $team);

        $team->loadMissing('members.roles', 'invitations');

        return new TeamResource($team);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamName $updateTeamName, Request $request, Team $team)
    {
        Gate::authorize('update', $team);

        $updateTeamName->handle($request, $team);

        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        Gate::authorize('delete', $team);

        //
    }
}
