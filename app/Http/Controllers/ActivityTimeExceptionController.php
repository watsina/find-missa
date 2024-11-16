<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use App\Models\ActivityTimeException;
use App\Http\Requests\ActivityTimeExceptionRequest;
use App\Http\Resources\ActivityTimeExceptionResource;
use App\Http\Resources\ActivityTimeExceptionCollection;

class ActivityTimeExceptionController extends Controller
{
    public function index(Request $request): ActivityTimeExceptionCollection
    {
        Gate::authorize('viewAny', ActivityTimeException::class);

        $activityTimeExceptions = ActivityTimeException::all();

        return new ActivityTimeExceptionCollection($activityTimeExceptions);
    }

    public function store(ActivityTimeExceptionRequest $request): ActivityTimeExceptionResource
    {
        Gate::authorize('create', ActivityTimeException::class);

        $activityTimeException = ActivityTimeException::create($request->validated());

        return new ActivityTimeExceptionResource($activityTimeException);
    }

    public function show(Request $request, ActivityTimeException $activityTimeException): ActivityTimeExceptionResource
    {
        Gate::authorize('view', $activityTimeException);

        return new ActivityTimeExceptionResource($activityTimeException);
    }

    public function update(ActivityTimeExceptionRequest $request, ActivityTimeException $activityTimeException): ActivityTimeExceptionResource
    {
        Gate::authorize('update', $activityTimeException);

        $activityTimeException->update($request->validated());

        return new ActivityTimeExceptionResource($activityTimeException);
    }

    public function destroy(Request $request, ActivityTimeException $activityTimeException): Response
    {
        Gate::authorize('delete', $activityTimeException);

        $activityTimeException->delete();

        return response()->noContent();
    }
}
