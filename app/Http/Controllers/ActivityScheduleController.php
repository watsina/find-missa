<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ActivitySchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\ActivityScheduleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\ActivitySchedule\StoreActivityScheduleRequest;
use App\Http\Requests\ActivitySchedule\UpdateActivityScheduleRequest;

class ActivityScheduleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ActivitySchedule::class);

        $items = abs(intval($request->itemsPerPage)) ?? 20;
        $itemsPerPage = $items > 100 ? 100 : $items;

        $activitySchedules = ActivitySchedule::search($request->search)
            ->query(function (Builder $query) use ($request) {
                $query->with('church:id,name', 'dayDetails', 'tags');

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

                // Filter by day pattern
                if ($request->has('day_pattern')) {
                    $query->where('day_pattern', $request->input('day_pattern'));
                }
            })
            ->paginate(perPage: $itemsPerPage, pageName: 'page', page: $request->page ?? 1);

        return ActivityScheduleResource::collection($activitySchedules);
    }

    public function store(StoreActivityScheduleRequest $request): ActivityScheduleResource
    {
        Gate::authorize('create', ActivitySchedule::class);

        $data = $request->validated();
        $dayDetails = $data['day_details'] ?? [];
        $tags = $data['tags'] ?? [];

        unset($data['day_details'], $data['tags']);

        $activitySchedule = DB::transaction(function () use ($data, $dayDetails, $tags) {
            $activitySchedule = ActivitySchedule::create($data);

            // Create day details
            foreach ($dayDetails as $detail) {
                $activitySchedule->dayDetails()->create($detail);
            }

            // Attach tags
            if (! empty($tags)) {
                $activitySchedule->tags()->attach($tags);
            }

            return $activitySchedule;
        });

        $activitySchedule->load('dayDetails', 'tags');

        return new ActivityScheduleResource($activitySchedule);
    }

    public function show(Request $request, ActivitySchedule $activitySchedule): ActivityScheduleResource
    {
        Gate::authorize('view', $activitySchedule);

        $activitySchedule->loadMissing('church', 'dayDetails', 'schedulePeriod', 'tags');

        return new ActivityScheduleResource($activitySchedule);
    }

    public function update(UpdateActivityScheduleRequest $request, ActivitySchedule $activitySchedule): ActivityScheduleResource
    {
        Gate::authorize('update', $activitySchedule);

        $data = $request->validated();
        $dayDetails = $data['day_details'] ?? null;
        $tags = $data['tags'] ?? null;

        unset($data['day_details'], $data['tags']);

        DB::transaction(function () use ($activitySchedule, $data, $dayDetails, $tags) {
            $activitySchedule->update($data);

            // Update day details if provided
            if ($dayDetails !== null) {
                // Delete existing day details
                $activitySchedule->dayDetails()->delete();

                // Create new day details
                foreach ($dayDetails as $detail) {
                    $activitySchedule->dayDetails()->create($detail);
                }
            }

            // Sync tags if provided
            if ($tags !== null) {
                $activitySchedule->tags()->sync($tags);
            }
        });

        $activitySchedule->load('dayDetails', 'tags');

        return new ActivityScheduleResource($activitySchedule);
    }

    public function destroy(Request $request, ActivitySchedule $activitySchedule): Response
    {
        Gate::authorize('delete', $activitySchedule);

        DB::transaction(function () use ($activitySchedule) {
            $activitySchedule->dayDetails()->delete();
            $activitySchedule->tags()->detach();
            $activitySchedule->delete();
        });

        return response()->noContent();
    }
}
