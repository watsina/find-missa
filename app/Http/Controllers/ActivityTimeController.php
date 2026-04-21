<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use App\Models\ActivityTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\ActivityTimeRequest;
use App\Http\Resources\ActivityTimeResource;
use App\Http\Resources\ActivityTimeCollection;

class ActivityTimeController extends Controller
{
    public function index(Request $request): ActivityTimeCollection
    {
        Gate::authorize('viewAny', ActivityTime::class);

        $activityTimes = ActivityTime::search($request->search)
            ->query(function (Builder $query) use ($request) {
                $query->with('church:id,name');

                // Filter by schedule period
                if ($request->has('schedule_period_id')) {
                    if ($request->input('schedule_period_id') === 'null' || $request->input('schedule_period_id') === '') {
                        $query->withoutSchedulePeriod();
                    } else {
                        $query->forSchedulePeriod((int) $request->input('schedule_period_id'));
                    }
                }

                // Filter by effective date
                if ($request->has('effective_on')) {
                    $query->effectiveOn($request->input('effective_on'));
                }

                // Filter by type
                if ($request->has('type')) {
                    $query->where('type', $request->input('type'));
                }

                // Filter by church
                if ($request->has('church_id')) {
                    $query->where('church_id', (int) $request->input('church_id'));
                }

                // Filter by occurrence
                if ($request->has('occurrence')) {
                    $query->where('occurrence', $request->input('occurrence'));
                }
            })
            ->paginate($perPage = $this->getItemsPerPage($request), $pageName = 'page', $page = $request->page ?? 1);

        return new ActivityTimeCollection($activityTimes);
    }

    public function store(ActivityTimeRequest $request)
    {
        Gate::authorize('create', ActivityTime::class);

        $data = $request->validated();
        $hasStartingTimes = $request->safe()->only('starting_times');

        if ($data['occurrence'] === 'weekly' && isset($data['days_of_week'])) {
            $info = Arr::except($data, ['day_of_week', 'days_of_week', 'starting_times']);
            $activities = [];

            foreach ($data['days_of_week'] as $day) {
                $info['day_of_week'] = $day;
                if ($hasStartingTimes) {
                    foreach ($data['starting_times'] as $time) {
                        $info['start_time'] = $time;
                        $activities[] = ActivityTime::create($info);
                    }
                } else {
                    $activities[] = ActivityTime::create($info);
                }
            }

            return (new ActivityTimeCollection($activities))->response()->setStatusCode(201);
        }

        if ($hasStartingTimes) {
            $info = $request->safe()->except('starting_times');
            foreach ($data['starting_times'] as $time) {
                $info['start_time'] = $time;
                $activities[] = ActivityTime::create($info);
            }

            return (new ActivityTimeCollection($activities))->response()->setStatusCode(201);
        }
        $activityTime = ActivityTime::create(Arr::except($data, ['starting_times']));

        return new ActivityTimeResource($activityTime);
    }

    public function show(Request $request, ActivityTime $activityTime): ActivityTimeResource
    {
        Gate::authorize('view', $activityTime);

        $activityTime->loadMissing('church', 'exceptions', 'schedulePeriod');

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
