<?php

namespace App\Http\Controllers;

use App\Models\SubParish;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\SubParishRequest;
use App\Http\Resources\SubParishResource;
use App\Http\Resources\SubParishCollection;

class SubParishController extends Controller
{
    public function index(Request $request): SubParishCollection
    {
        $subParishes = SubParish::with('parish')->paginate(20);

        return new SubParishCollection($subParishes);
    }

    public function store(SubParishRequest $request): SubParishResource
    {
        $subParish = SubParish::create($request->validated());

        $subParish->loadMissing('parish');

        return new SubParishResource($subParish);
    }

    public function show(Request $request, SubParish $subParish): SubParishResource
    {
        $subParish->loadMissing('parish');

        return new SubParishResource($subParish);
    }

    public function update(SubParishRequest $request, SubParish $subParish): SubParishResource
    {
        $subParish->update($request->validated());

        $subParish->loadMissing('parish');

        return new SubParishResource($subParish);
    }

    public function destroy(Request $request, SubParish $subParish): Response
    {
        $subParish->delete();

        return response()->noContent();
    }
}
