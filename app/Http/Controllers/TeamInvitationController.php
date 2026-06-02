<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\TeamInvitationResource;
use App\Http\Resources\TeamInvitationCollection;
use App\Http\Requests\StoreTeamInvitationRequest;

class TeamInvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', TeamMember::class);

        $invitations = TeamInvitation::all();

        return new TeamInvitationCollection($invitations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamInvitationRequest $request, Team $team)
    {
        Gate::authorize('create', TeamMember::class);

        $invitation = $team->invitations()->create($request->validated());

        return new TeamInvitationResource($invitation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team, TeamInvitation $invitation)
    {
        $invitation->delete();

        return response()->noContent();
    }
}
