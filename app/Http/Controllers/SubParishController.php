<?php

namespace App\Http\Controllers;

use App\Models\SubParish;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\SubParishRequest;
use App\Http\Resources\SubParishResource;
use App\Http\Resources\SubParishCollection;

class SubParishController extends Controller
{
    public function index(Request $request): SubParishCollection
    {
        Gate::authorize('viewAny', SubParish::class);

        $items = abs(intval($request->itemsPerPage)) ?? 20;
        $itemsPerPage = $items > 100 ? 100 : $items;

        $subParishes = SubParish::search($request->search)
            ->query(fn ($q) => $q->with('parish')->withCount('churches'))
            ->paginate($perPage = $itemsPerPage, $pageName = 'page', $page = $request->page ?? 1);

        return new SubParishCollection($subParishes);
    }

    public function store(SubParishRequest $request): SubParishResource
    {
        Gate::authorize('view', SubParish::class);

        $subParish = SubParish::create($request->validated());

        $subParish->loadMissing('parish');

        return new SubParishResource($subParish);
    }

    public function show(Request $request, SubParish $subParish): SubParishResource
    {
        Gate::authorize('view', $subParish);

        $subParish->loadMissing('parish', 'churches');

        return new SubParishResource($subParish);
    }

    public function update(SubParishRequest $request, SubParish $subParish): SubParishResource
    {
        Gate::authorize('update', $subParish);

        $subParish->update($request->validated());

        return new SubParishResource($subParish);
    }

    public function destroy(Request $request, SubParish $subParish): Response
    {
        Gate::authorize('delete', $subParish);

        $subParish->delete();

        return response()->noContent();
    }

    public function syncCodeName(Request $request, SubParish $subParish): SubParishResource
    {
        Gate::authorize('update', $subParish);

        $subParish->generateCodeName();
        $subParish->save();

        return new SubParishResource($subParish);
    }
}
