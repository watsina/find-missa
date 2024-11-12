<?php

namespace App\Http\Controllers\Acl;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\PermissionCollection;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::all();

        return new PermissionCollection($permissions);
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
