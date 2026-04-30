<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\TeamMemberResource;
use App\Http\Resources\TeamMemberCollection;
use App\Http\Requests\UpdateTeamMemberRequest;

class TeamMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', TeamMember::class);

        $members = TeamMember::with('user')->paginate(20);

        return new TeamMemberCollection($members);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team, TeamMember $member)
    {
        Gate::authorize('view', $member);

        $member->loadMissing(['user' => function ($query) {
            $query->with('roles');
        }]);

        return new TeamMemberResource($member);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamMemberRequest $request, Team $team, TeamMember $member)
    {
        Gate::authorize('update', $member);

        $member->loadMissing('user');

        if (! $member->user) {
            abort(422, 'Team member has no associated user');
        }

        $member->user->syncRoles($request->validated('role_ids'));

        $member->load(['user' => function ($query) {
            $query->with('roles');
        }]);

        return new TeamMemberResource($member);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team, TeamMember $member)
    {
        Gate::authorize('delete', $member);

        $member->delete();

        return response()->noContent();
    }
}
