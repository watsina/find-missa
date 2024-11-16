<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\TeamInvitationResource;
use App\Http\Resources\TeamInvitationCollection;

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
    public function store(Request $request, Team $team)
    {
        Gate::authorize('create', TeamMember::class);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users|unique:team_invitations',
            'role_id' => 'required|exists:roles,id',
        ]);

        $invitation = $team->invitations()->create($data);

        return new TeamInvitationResource($invitation);
    }

    /**
     * Display the specified resource.
     */
    public function show(TeamInvitation $userInvitation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeamInvitation $userInvitation)
    {
        //
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
