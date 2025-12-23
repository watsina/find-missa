<?php

namespace App\Http\Controllers;

use App\Models\ActivityTag;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\ActivityTagResource;
use App\Http\Requests\ActivityTag\StoreActivityTagRequest;
use App\Http\Requests\ActivityTag\UpdateActivityTagRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ActivityTag::class);

        $query = ActivityTag::query()
            ->availableForTeam($request->user()->currentTeam?->id);

        // Filter by category if provided
        if ($request->has('category')) {
            $query->ofCategory($request->category);
        }

        // Filter by system/custom
        if ($request->has('is_system')) {
            $request->boolean('is_system')
                ? $query->system()
                : $query->custom();
        }

        // Filter by user visibility
        if ($request->has('is_user_visible')) {
            if ($request->boolean('is_user_visible')) {
                $query->userVisible();
            }
        }

        $activityTags = $query->orderBy('name')->get();

        return ActivityTagResource::collection($activityTags);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivityTagRequest $request): ActivityTagResource
    {
        Gate::authorize('create', ActivityTag::class);

        $data = $request->validated();

        // Custom tags are never system tags
        $data['is_system'] = false;
        $data['is_user_visible'] = true;

        $activityTag = ActivityTag::create($data);

        return new ActivityTagResource($activityTag);
    }

    /**
     * Display the specified resource.
     */
    public function show(ActivityTag $activityTag): ActivityTagResource
    {
        Gate::authorize('view', $activityTag);

        return new ActivityTagResource($activityTag);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActivityTagRequest $request, ActivityTag $activityTag): ActivityTagResource
    {
        // System tags cannot be updated, even by admins
        if ($activityTag->is_system) {
            abort(HttpResponse::HTTP_FORBIDDEN, 'System tags cannot be updated.');
        }

        Gate::authorize('update', $activityTag);

        $activityTag->update($request->validated());

        return new ActivityTagResource($activityTag);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActivityTag $activityTag): Response
    {
        // System tags cannot be deleted, even by admins
        if ($activityTag->is_system) {
            abort(HttpResponse::HTTP_FORBIDDEN, 'System tags cannot be deleted.');
        }

        Gate::authorize('delete', $activityTag);

        // Detach from all activity times before deleting
        $activityTag->activityTimes()->detach();

        $activityTag->delete();

        return response()->noContent();
    }
}
