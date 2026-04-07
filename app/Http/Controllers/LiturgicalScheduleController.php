<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\LiturgicalSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\LiturgicalScheduleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\LiturgicalSchedule\StoreLiturgicalScheduleRequest;
use App\Http\Requests\LiturgicalSchedule\UpdateLiturgicalScheduleRequest;

class LiturgicalScheduleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', LiturgicalSchedule::class);

        $items = abs(intval($request->itemsPerPage)) ?: 20;
        $itemsPerPage = $items > 100 ? 100 : $items;

        $liturgicalSchedules = LiturgicalSchedule::query()
            ->with('church:id,name', 'holiday:id,name,slug,type', 'times')
            ->when($request->has('church_id'), function (Builder $query) use ($request) {
                $query->forChurch((int) $request->input('church_id'));
            })
            ->when($request->has('holiday_id'), function (Builder $query) use ($request) {
                $query->forHoliday((int) $request->input('holiday_id'));
            })
            ->when($request->has('year'), function (Builder $query) use ($request) {
                $query->forYear((int) $request->input('year'));
            })
            ->orderBy('year', 'desc')
            ->paginate(perPage: $itemsPerPage, pageName: 'page', page: $request->page ?? 1);

        return LiturgicalScheduleResource::collection($liturgicalSchedules);
    }

    public function store(StoreLiturgicalScheduleRequest $request): LiturgicalScheduleResource
    {
        Gate::authorize('create', LiturgicalSchedule::class);

        $data = $request->validated();
        $times = $data['times'] ?? [];

        unset($data['times']);

        $liturgicalSchedule = DB::transaction(function () use ($data, $times) {
            $liturgicalSchedule = LiturgicalSchedule::create($data);

            foreach ($times as $time) {
                $liturgicalSchedule->times()->create($time);
            }

            return $liturgicalSchedule;
        });

        $liturgicalSchedule->load('times', 'holiday');

        return new LiturgicalScheduleResource($liturgicalSchedule);
    }

    public function show(Request $request, LiturgicalSchedule $liturgicalSchedule): LiturgicalScheduleResource
    {
        Gate::authorize('view', $liturgicalSchedule);

        $liturgicalSchedule->loadMissing('church', 'holiday', 'times');

        return new LiturgicalScheduleResource($liturgicalSchedule);
    }

    public function update(UpdateLiturgicalScheduleRequest $request, LiturgicalSchedule $liturgicalSchedule): LiturgicalScheduleResource
    {
        Gate::authorize('update', $liturgicalSchedule);

        $data = $request->validated();
        $times = $data['times'] ?? null;

        unset($data['times']);

        DB::transaction(function () use ($liturgicalSchedule, $data, $times) {
            $liturgicalSchedule->update($data);

            // Update times if provided
            if ($times !== null) {
                // Delete existing times
                $liturgicalSchedule->times()->delete();

                // Create new times
                foreach ($times as $time) {
                    $liturgicalSchedule->times()->create($time);
                }
            }
        });

        $liturgicalSchedule->load('times', 'holiday');

        return new LiturgicalScheduleResource($liturgicalSchedule);
    }

    public function destroy(Request $request, LiturgicalSchedule $liturgicalSchedule): Response
    {
        Gate::authorize('delete', $liturgicalSchedule);

        DB::transaction(function () use ($liturgicalSchedule) {
            $liturgicalSchedule->times()->delete();
            $liturgicalSchedule->delete();
        });

        return response()->noContent();
    }
}
