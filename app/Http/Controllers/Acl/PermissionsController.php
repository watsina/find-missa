<?php

namespace App\Http\Controllers\Acl;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\PermissionCollection;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('roles.read');

        $permissions = Permission::all();

        return new PermissionCollection($permissions);
    }

    /**
     * Get permissions grouped by category from config.
     */
    public function categories(): JsonResponse
    {
        $configPermissions = config('acl.permissions', []);
        $categoryLabels = config('acl.permission_categories', []);

        $grouped = [];

        foreach ($configPermissions as $categoryKey => $permissions) {
            $permissionNames = collect($permissions)->pluck('name')->toArray();

            $dbPermissions = Permission::whereIn('name', $permissionNames)->get();

            $grouped[] = [
                'key' => $categoryKey,
                'label' => $categoryLabels[$categoryKey] ?? ucfirst($categoryKey),
                'permissions' => $dbPermissions->map(fn ($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'description' => $permission->description,
                ])->values(),
            ];
        }

        return response()->json(['data' => $grouped]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|lowercase|alpha_dash|unique:permissions,name',
            'description' => 'required|string|max:255',
        ]);

        $permission = Permission::create($data);

        return new PermissionResource($permission);
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        Gate::authorize('roles.read');

        return new PermissionResource($permission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'lowercase',
                'alpha_dash',
                Rule::unique('permissions', 'name')->ignore($permission),
            ],
            'description' => 'required|string|max:255',
        ]);

        $permission->update($data);

        return new PermissionResource($permission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        //
    }
}
