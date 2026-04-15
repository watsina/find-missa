<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\SubParish;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\ChurchRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\ChurchResource;
use App\Http\Resources\ChurchCollection;

class ChurchController extends Controller
{
    public function index(Request $request): ChurchCollection
    {
        Gate::authorize('viewAny', Church::class);

        $items = abs(intval($request->itemsPerPage)) ?: 20;
        $itemsPerPage = $items > 100 ? 100 : $items;

        $churches = Church::search($request->search)
            ->query(fn ($q) => $q->with('parishable:id,name,city'))
            ->paginate($perPage = $itemsPerPage, $pageName = 'page', $page = $request->page ?? 1);

        return new ChurchCollection($churches);
    }

    public function store(ChurchRequest $request): ChurchResource
    {
        Gate::authorize('create', Church::class);

        $church = Church::create($request->safe()->except('parishable_id'));
        $data = $request->validated();

        $parish = $data['type'] === 'sub-parish'
            ? SubParish::findOrFail($data['parishable_id'])
            : Parish::findOrFail($data['parishable_id']);

        $church->parishable()->associate($parish)->save();

        $church->loadMissing('activities', 'parishable:id,name,city');

        return new ChurchResource($church);
    }

    public function show(Request $request, Church $church): ChurchResource
    {
        Gate::authorize('view', $church);

        $church->loadMissing('activities', 'parishable:id,name,city');

        return new ChurchResource($church);
    }

    public function update(ChurchRequest $request, Church $church): ChurchResource
    {
        Gate::authorize('update', $church);

        $church->update($request->safe()->except('parishable_id'));
        $data = $request->validated();

        if (isset($data['parishable_id'])) {
            $parish = $data['type'] === 'sub-parish'
                ? SubParish::findOrFail($data['parishable_id'])
                : Parish::findOrFail($data['parishable_id']);

            $church->parishable()->associate($parish)->save();
        }

        $church->loadMissing('activities', 'parishable:id,name,city');

        return new ChurchResource($church);
    }

    public function destroy(Request $request, Church $church): Response
    {
        Gate::authorize('delete', $church);

        // first make sure the user has deleted all the activites
        $church->delete();

        return response()->noContent();
    }

    public function syncCodeName(Request $request, Church $church): ChurchResource
    {
        Gate::authorize('update', $church);

        $church->generateCodeName();
        $church->save();

        return new ChurchResource($church);
    }
}
