<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamInvitation;
use App\Http\Resources\TeamInvitationResource;
use App\Http\Resources\TeamInvitationCollection;

class TeamInvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invitations = TeamInvitation::all();

        return new TeamInvitationCollection($invitations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users|unique:user_invitations',
        ]);

        $invitation = TeamInvitation::create($data);

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
    public function destroy(TeamInvitation $userInvitation)
    {
        $userInvitation->delete();

        return response()->noContent();
    }
}
