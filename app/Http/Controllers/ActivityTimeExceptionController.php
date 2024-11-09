<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ActivityTimeException;
use App\Http\Requests\ActivityTimeExceptionRequest;
use App\Http\Resources\ActivityTimeExceptionResource;
use App\Http\Resources\ActivityTimeExceptionCollection;

class ActivityTimeExceptionController extends Controller
{
    public function index(Request $request): ActivityTimeExceptionCollection
    {
        $activityTimeExceptions = ActivityTimeException::all();

        return new ActivityTimeExceptionCollection($activityTimeExceptions);
    }

    public function store(ActivityTimeExceptionRequest $request): ActivityTimeExceptionResource
    {
        $activityTimeException = ActivityTimeException::create($request->validated());

        return new ActivityTimeExceptionResource($activityTimeException);
    }

    public function show(Request $request, ActivityTimeException $activityTimeException): ActivityTimeExceptionResource
    {
        return new ActivityTimeExceptionResource($activityTimeException);
    }

    public function update(ActivityTimeExceptionRequest $request, ActivityTimeException $activityTimeException): ActivityTimeExceptionResource
    {
        $activityTimeException->update($request->validated());

        return new ActivityTimeExceptionResource($activityTimeException);
    }

    public function destroy(Request $request, ActivityTimeException $activityTimeException): Response
    {
        $activityTimeException->delete();

        return response()->noContent();
    }
}
