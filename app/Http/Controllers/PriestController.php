<?php

namespace App\Http\Controllers;

use App\Models\Priest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\PriestResource;
use App\Http\Resources\PriestCollection;
use App\Http\Requests\PriestStoreRequest;
use App\Http\Requests\PriestUpdateRequest;

class PriestController extends Controller
{
    public function index(Request $request): PriestCollection
    {
        $priests = Priest::all();

        return new PriestCollection($priests);
    }

    public function store(PriestStoreRequest $request): PriestResource
    {
        $priest = Priest::create($request->validated());

        return new PriestResource($priest);
    }

    public function show(Request $request, Priest $priest): PriestResource
    {
        return new PriestResource($priest);
    }

    public function update(PriestUpdateRequest $request, Priest $priest): PriestResource
    {
        $priest->update($request->validated());

        return new PriestResource($priest);
    }

    public function destroy(Request $request, Priest $priest): Response
    {
        $priest->delete();

        return response()->noContent();
    }
}
