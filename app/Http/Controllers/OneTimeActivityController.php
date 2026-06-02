<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\OneTimeActivity;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\OneTimeActivityResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\OneTimeActivity\StoreOneTimeActivityRequest;
use App\Http\Requests\OneTimeActivity\UpdateOneTimeActivityRequest;

class OneTimeActivityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', OneTimeActivity::class);

        $oneTimeActivities = OneTimeActivity::query()
            ->with('church:id,name')
            ->when($request->has('church_id'), function (Builder $query) use ($request) {
                $query->forChurch((int) $request->input('church_id'));
            })
            ->when($request->has('type'), function (Builder $query) use ($request) {
                $query->where('type', $request->input('type'));
            })
            ->when($request->has('date'), function (Builder $query) use ($request) {
                $query->onDate($request->input('date'));
            })
            ->when($request->has('date_from'), function (Builder $query) use ($request) {
                $query->whereDate('date', '>=', $request->input('date_from'));
            })
            ->when($request->has('date_to'), function (Builder $query) use ($request) {
                $query->whereDate('date', '<=', $request->input('date_to'));
            })
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate(perPage: $this->getItemsPerPage($request), pageName: 'page', page: $request->page ?? 1);

        return OneTimeActivityResource::collection($oneTimeActivities);
    }

    public function store(StoreOneTimeActivityRequest $request): OneTimeActivityResource
    {
        Gate::authorize('create', OneTimeActivity::class);

        $oneTimeActivity = OneTimeActivity::create($request->validated());

        return new OneTimeActivityResource($oneTimeActivity);
    }

    public function show(Request $request, OneTimeActivity $oneTimeActivity): OneTimeActivityResource
    {
        Gate::authorize('view', $oneTimeActivity);

        $oneTimeActivity->loadMissing('church');

        return new OneTimeActivityResource($oneTimeActivity);
    }

    public function update(UpdateOneTimeActivityRequest $request, OneTimeActivity $oneTimeActivity): OneTimeActivityResource
    {
        Gate::authorize('update', $oneTimeActivity);

        $oneTimeActivity->update($request->validated());

        return new OneTimeActivityResource($oneTimeActivity);
    }

    public function destroy(Request $request, OneTimeActivity $oneTimeActivity): Response
    {
        Gate::authorize('delete', $oneTimeActivity);

        $oneTimeActivity->delete();

        return response()->noContent();
    }
}
