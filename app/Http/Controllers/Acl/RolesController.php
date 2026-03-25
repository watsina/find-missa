<?php

namespace App\Http\Controllers\Acl;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RoleCollection;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): RoleCollection
    {
        $roles = Role::query()
            ->with('permissions')
            ->paginate(20);

        return new RoleCollection($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RoleResource
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'team_id' => $request->user()->currentTeam->id,
        ]);

        if ($request->validated('permissions')) {
            $role->syncPermissions($request->validated('permissions'));
        }

        $role->loadMissing('permissions');

        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): RoleResource
    {
        $role->loadMissing('permissions');

        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $role->update($request->safe()->only(['name', 'description']));

        if ($request->has('permissions')) {
            $role->syncPermissions($request->validated('permissions') ?? []);
        }

        $role->loadMissing('permissions');

        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        $user = request()->user();
        $team = $user->currentTeam;

        // Ensure role belongs to current team
        if ($role->team_id !== $team->id) {
            abort(403, 'This role does not belong to your team.');
        }

        // Only admins or team owners can delete roles
        if (! $user->is_admin && ! $user->ownsTeam($team)) {
            abort(403, 'You do not have permission to delete roles.');
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
