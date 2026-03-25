<?php

namespace App\Http\Controllers\Acl;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class PermissionsController extends Controller
{
    /**
     * Display a listing of all permissions.
     */
    public function index(): JsonResponse
    {
        Gate::authorize('roles.read');

        $permissions = Permission::all()->map->toArray();

        return response()->json(['data' => $permissions->all()]);
    }

    /**
     * Get permissions grouped by category from config.
     */
    public function categories(): JsonResponse
    {
        return response()->json(['data' => Permission::groupedByCategory()]);
    }
}
