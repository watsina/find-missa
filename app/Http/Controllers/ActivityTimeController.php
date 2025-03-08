<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
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

        $items = abs(intval($request->itemsPerPage)) ?? 20;
        $itemsPerPage = $items > 100 ? 100 : $items;

        $activityTimes = ActivityTime::search($request->search)
            ->query(fn ($q) => $q->with('church:id,name'))
            ->paginate($perPage = $itemsPerPage, $pageName = 'page', $page = $request->page ?? 1);

        return new ActivityTimeCollection($activityTimes);
    }

    public function store(ActivityTimeRequest $request)
    {
        Gate::authorize('create', ActivityTime::class);

        $data = $request->validated();

        if ($data['occurrence'] === 'weekly' && isset($data['days_of_week'])) {
            $info = Arr::except($data, ['day_of_week', 'days_of_week']);
            $activities = [];

            foreach ($data['days_of_week'] as $day) {
                $activities[] = ActivityTime::create([...$info, 'day_of_week' => $day]);
            }

            return (new ActivityTimeCollection($activities))->response()->setStatusCode(201);
        }
        $activityTime = ActivityTime::create($data);

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
