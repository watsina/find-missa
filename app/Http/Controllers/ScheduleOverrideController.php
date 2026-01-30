<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ScheduleOverride;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\ScheduleOverrideResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\ScheduleOverride\StoreScheduleOverrideRequest;
use App\Http\Requests\ScheduleOverride\UpdateScheduleOverrideRequest;

class ScheduleOverrideController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ScheduleOverride::class);

        $items = abs(intval($request->itemsPerPage)) ?? 20;
        $itemsPerPage = $items > 100 ? 100 : $items;

        $scheduleOverrides = ScheduleOverride::query()
            ->when($request->has('scope_type'), function (Builder $query) use ($request) {
                $query->where('scope_type', $request->input('scope_type'));
            })
            ->when($request->has('scope_id'), function (Builder $query) use ($request) {
                $query->where('scope_id', (int) $request->input('scope_id'));
            })
            ->when($request->has('church_id'), function (Builder $query) use ($request) {
                $query->forChurch((int) $request->input('church_id'));
            })
            ->when($request->has('diocese_id'), function (Builder $query) use ($request) {
                $query->forDiocese((int) $request->input('diocese_id'));
            })
            ->when($request->has('date'), function (Builder $query) use ($request) {
                $query->coversDate($request->input('date'));
            })
            ->when($request->has('is_active'), function (Builder $query) use ($request) {
                $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('start_date', 'asc')
            ->paginate($perPage = $itemsPerPage, $pageName = 'page', $page = $request->page ?? 1);

        return ScheduleOverrideResource::collection($scheduleOverrides);
    }

    public function store(StoreScheduleOverrideRequest $request): ScheduleOverrideResource
    {
        Gate::authorize('create', ScheduleOverride::class);

        $scheduleOverride = ScheduleOverride::create($request->validated());

        return new ScheduleOverrideResource($scheduleOverride);
    }

    public function show(Request $request, ScheduleOverride $scheduleOverride): ScheduleOverrideResource
    {
        Gate::authorize('view', $scheduleOverride);

        return new ScheduleOverrideResource($scheduleOverride);
    }

    public function update(UpdateScheduleOverrideRequest $request, ScheduleOverride $scheduleOverride): ScheduleOverrideResource
    {
        Gate::authorize('update', $scheduleOverride);

        $scheduleOverride->update($request->validated());

        return new ScheduleOverrideResource($scheduleOverride);
    }

    public function destroy(Request $request, ScheduleOverride $scheduleOverride): Response
    {
        Gate::authorize('delete', $scheduleOverride);

        $scheduleOverride->delete();

        return response()->noContent();
    }
}
