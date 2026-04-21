<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchedulePeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\SchedulePeriodResource;
use App\Http\Requests\StoreSchedulePeriodRequest;
use App\Http\Requests\UpdateSchedulePeriodRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SchedulePeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', SchedulePeriod::class);

        $teamId = $request->user()->currentTeam?->id;

        $periods = SchedulePeriod::search($request->search)
            ->query(function ($query) use ($request, $teamId) {
                $query->availableForTeam($teamId)
                    ->orderBy('start_date', 'desc');

                // Filter by active status
                if ($request->has('is_active')) {
                    $query->where('is_active', $request->boolean('is_active'));
                }

                // Filter by current (in effect now)
                if ($request->boolean('current')) {
                    $query->current();
                }

                // Filter by upcoming
                if ($request->boolean('upcoming')) {
                    $query->upcoming();
                }

                // Filter by recurring
                if ($request->has('is_recurring')) {
                    $query->where('is_recurring', $request->boolean('is_recurring'));
                }

                // Filter by date range overlap
                if ($request->has('overlaps_start') && $request->has('overlaps_end')) {
                    $query->overlapping($request->input('overlaps_start'), $request->input('overlaps_end'));
                }
            })
            ->paginate($perPage = $this->getItemsPerPage($request), $pageName = 'page', $page = $request->page ?? 1);

        return SchedulePeriodResource::collection($periods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchedulePeriodRequest $request): SchedulePeriodResource
    {
        $data = $request->validated();

        // Handle is_universal flag - if true, set team_id to null
        if ($request->boolean('is_universal')) {
            $data['team_id'] = null;
        } else {
            $data['team_id'] = $request->user()->currentTeam?->id;
        }
        unset($data['is_universal']);

        $schedulePeriod = SchedulePeriod::create($data);

        return new SchedulePeriodResource($schedulePeriod);
    }

    /**
     * Display the specified resource.
     */
    public function show(SchedulePeriod $schedulePeriod): SchedulePeriodResource
    {
        Gate::authorize('view', $schedulePeriod);

        return new SchedulePeriodResource($schedulePeriod);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchedulePeriodRequest $request, SchedulePeriod $schedulePeriod): SchedulePeriodResource
    {
        $data = $request->validated();

        $schedulePeriod->update($data);

        return new SchedulePeriodResource($schedulePeriod->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchedulePeriod $schedulePeriod): JsonResponse
    {
        Gate::authorize('delete', $schedulePeriod);

        $schedulePeriod->delete();

        return response()->json(null, 204);
    }

    /**
     * Get schedule periods that are currently active.
     */
    public function current(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', SchedulePeriod::class);

        $teamId = $request->user()->currentTeam?->id;

        $periods = SchedulePeriod::query()
            ->availableForTeam($teamId)
            ->active()
            ->current()
            ->orderBy('start_date')
            ->get();

        return SchedulePeriodResource::collection($periods);
    }

    /**
     * Get upcoming schedule periods.
     */
    public function upcoming(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', SchedulePeriod::class);

        $teamId = $request->user()->currentTeam?->id;

        $periods = SchedulePeriod::query()
            ->availableForTeam($teamId)
            ->active()
            ->upcoming()
            ->orderBy('start_date')
            ->get();

        return SchedulePeriodResource::collection($periods);
    }

    /**
     * Check for overlapping schedule periods with a given date range.
     */
    public function checkOverlap(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', SchedulePeriod::class);

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'exclude_id' => ['nullable', 'integer', 'exists:schedule_periods,id'],
        ]);

        $teamId = $request->user()->currentTeam?->id;

        $query = SchedulePeriod::query()
            ->availableForTeam($teamId)
            ->overlapping($request->input('start_date'), $request->input('end_date'));

        if ($request->has('exclude_id')) {
            $query->where('id', '!=', $request->input('exclude_id'));
        }

        $overlapping = $query->get();

        return response()->json([
            'has_overlap' => $overlapping->isNotEmpty(),
            'overlapping_periods' => SchedulePeriodResource::collection($overlapping),
        ]);
    }
}
