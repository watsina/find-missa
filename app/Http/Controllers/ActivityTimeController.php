<?php

namespace App\Http\Controllers;

use App\Models\ActivityTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\ActivityTimeRequest;
use App\Http\Resources\ActivityTimeResource;
use App\Http\Resources\ActivityTimeCollection;

class ActivityTimeController extends Controller
{
    public function index(Request $request): ActivityTimeCollection
    {
        Gate::authorize('viewAny', ActivityTime::class);

        $activityTimes = ActivityTime::paginate(20);

        return new ActivityTimeCollection($activityTimes);
    }

    public function store(ActivityTimeRequest $request): ActivityTimeResource
    {
        Gate::authorize('create', ActivityTime::class);

        $activityTime = ActivityTime::create($request->validated());

        return new ActivityTimeResource($activityTime);
    }

    public function show(Request $request, ActivityTime $activityTime): ActivityTimeResource
    {
        Gate::authorize('view', $activityTime);

        $activityTime->loadMissing('church', 'exceptions');

        return new ActivityTimeResource($activityTime);
    }

    public function update(ActivityTimeRequest $request, ActivityTime $activityTime): ActivityTimeResource
    {
        Gate::authorize('update', $activityTime);

        $activityTime->update($request->validated());

        return new ActivityTimeResource($activityTime);
    }

    public function destroy(Request $request, ActivityTime $activityTime): Response
    {
        Gate::authorize('delete', $activityTime);

        $activityTime->delete();

        return response()->noContent();
    }
}
