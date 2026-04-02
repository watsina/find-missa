<?php

namespace App\Http\Controllers;

use App\Models\Parish;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\ParishRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\ParishResource;
use App\Http\Resources\ParishCollection;

class ParishController extends Controller
{
    public function index(Request $request): ParishCollection
    {
        Gate::authorize('viewAny', Parish::class);

        $items = abs(intval($request->itemsPerPage)) ?: 20;
        $itemsPerPage = $items > 100 ? 100 : $items;

        $parishes = Parish::search($request->search)
            ->query(fn ($q) => $q->with('diocese')->withCount('churches', 'subParishes'))
            ->paginate($perPage = $itemsPerPage, $pageName = 'page', $page = $request->page ?? 1);

        return new ParishCollection($parishes);
    }

    public function store(ParishRequest $request): ParishResource
    {
        Gate::authorize('create', Parish::class);

        $parish = Parish::create($request->validated());

        $parish->loadMissing('diocese');

        return new ParishResource($parish);
    }

    public function show(Request $request, Parish $parish): ParishResource
    {
        Gate::authorize('view', $parish);

        $parish->loadMissing('diocese', 'subParishes', 'churches', 'priests');

        return new ParishResource($parish);
    }

    public function update(ParishRequest $request, Parish $parish): ParishResource
    {
        Gate::authorize('update', $parish);

        $parish->update($request->validated());

        $parish->loadMissing('diocese');

        return new ParishResource($parish);
    }

    public function destroy(Request $request, Parish $parish): Response
    {
        Gate::authorize('delete', $parish);

        $parish->delete();

        return response()->noContent();
    }

    public function syncCodeName(Request $request, Parish $parish): ParishResource
    {
        Gate::authorize('update', $parish);

        $parish->generateCodeName();
        $parish->save();

        return new ParishResource($parish);
    }
}
