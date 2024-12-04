<?php

namespace App\Http\Controllers;

use App\Models\Priest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\PriestResource;
use App\Http\Resources\PriestCollection;
use App\Http\Requests\PriestStoreRequest;
use App\Http\Requests\PriestUpdateRequest;

class PriestController extends Controller
{
    public function index(Request $request): PriestCollection
    {
        Gate::authorize('viewAny', Priest::class);

        $priests = Priest::with('parish:id,name,city')->paginate(20);

        return new PriestCollection($priests);
    }

    public function store(PriestStoreRequest $request): PriestResource
    {
        Gate::authorize('create', Priest::class);

        $priest = Priest::create($request->validated());
        $priest->loadMissing('parish:id,name,city');

        return new PriestResource($priest);
    }

    public function show(Request $request, Priest $priest): PriestResource
    {
        Gate::authorize('view', $priest);
        $priest->loadMissing('parish:id,name,city');

        return new PriestResource($priest);
    }

    public function update(PriestUpdateRequest $request, Priest $priest): PriestResource
    {
        Gate::authorize('update', $priest);

        $priest->update($request->validated());

        return new PriestResource($priest);
    }

    public function destroy(Request $request, Priest $priest): Response
    {
        Gate::authorize('delete', $priest);

        $priest->delete();

        return response()->noContent();
    }
}
