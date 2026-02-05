<?php

namespace App\Http\Controllers;

use App\Models\ScheduleRule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\ScheduleRuleResource;
use App\Http\Requests\ScheduleRule\StoreScheduleRuleRequest;
use App\Http\Requests\ScheduleRule\UpdateScheduleRuleRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScheduleRuleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ScheduleRule::class);

        $items = abs(intval($request->itemsPerPage)) ?? 20;
        $itemsPerPage = $items > 100 ? 100 : $items;

        $scheduleRules = ScheduleRule::query()
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
            ->when($request->has('condition_type'), function (Builder $query) use ($request) {
                $query->where('condition_type', $request->input('condition_type'));
            })
            ->when($request->has('is_active'), function (Builder $query) use ($request) {
                $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
            })
            ->orderedByPriority()
            ->paginate(perPage: $itemsPerPage, pageName: 'page', page: $request->page ?? 1);

        return ScheduleRuleResource::collection($scheduleRules);
    }

    public function store(StoreScheduleRuleRequest $request): ScheduleRuleResource
    {
        Gate::authorize('create', ScheduleRule::class);

        $scheduleRule = ScheduleRule::create($request->validated());

        return new ScheduleRuleResource($scheduleRule);
    }

    public function show(Request $request, ScheduleRule $scheduleRule): ScheduleRuleResource
    {
        Gate::authorize('view', $scheduleRule);

        return new ScheduleRuleResource($scheduleRule);
    }

    public function update(UpdateScheduleRuleRequest $request, ScheduleRule $scheduleRule): ScheduleRuleResource
    {
        Gate::authorize('update', $scheduleRule);

        $scheduleRule->update($request->validated());

        return new ScheduleRuleResource($scheduleRule);
    }

    public function destroy(Request $request, ScheduleRule $scheduleRule): Response
    {
        Gate::authorize('delete', $scheduleRule);

        $scheduleRule->delete();

        return response()->noContent();
    }
}
