<?php

namespace App\Http\Controllers;

use App\Models\ActivityTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\ActivityTimeResource;
use App\Http\Resources\ActivityTimeCollection;
use App\Http\Requests\ActivityTimeStoreRequest;
use App\Http\Requests\ActivityTimeUpdateRequest;

class ActivityTimeController extends Controller
{
    public function index(Request $request): ActivityTimeCollection
    {
        $activityTimes = ActivityTime::all();

        return new ActivityTimeCollection($activityTimes);
    }

    public function store(ActivityTimeStoreRequest $request): ActivityTimeResource
    {
        $activityTime = ActivityTime::create($request->validated());

        return new ActivityTimeResource($activityTime);
    }

    public function show(Request $request, ActivityTime $activityTime): ActivityTimeResource
    {
        return new ActivityTimeResource($activityTime);
    }

    public function update(ActivityTimeUpdateRequest $request, ActivityTime $activityTime): ActivityTimeResource
    {
        $activityTime->update($request->validated());

        return new ActivityTimeResource($activityTime);
    }

    public function destroy(Request $request, ActivityTime $activityTime): Response
    {
        $activityTime->delete();

        return response()->noContent();
    }
}
