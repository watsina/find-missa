<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use App\Http\Resources\TeamMemberResource;
use App\Http\Resources\TeamMemberCollection;

class TeamMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $members = TeamMember::all();

        return new TeamMemberCollection($members);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team, TeamMember $member)
    {

        return new TeamMemberResource($member);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team, TeamMember $member)
    {
        $data = $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'required|exists:roles,id',
        ]);

        $member->user->syncRoles($data['role_ids']);

        return new TeamMemberResource($member);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team, TeamMember $member)
    {
        $member->delete();

        return response()->noContent();
    }
}
