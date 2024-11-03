<?php

namespace App\Http\Controllers\Acl;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RoleCollection;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();

        return new RoleCollection($roles);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'ability_ids' => 'required|array',
            'ability_ids.*' => 'required|exists:abilities,id',
        ]);

        $role->abilities()->sync($data['ability_ids']);

        return new RoleResource($role);
    }
}
