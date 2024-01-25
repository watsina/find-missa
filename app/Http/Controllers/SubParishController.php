<?php

namespace App\Http\Controllers;

use App\Models\SubParish;
use Illuminate\Http\Request;
use App\Http\Resources\SubParishResource;
use App\Http\Resources\SubParishCollection;
use App\Http\Requests\SubParishStoreRequest;
use App\Http\Requests\SubParishUpdateRequest;

class SubParishController extends Controller
{
    public function index(Request $request): SubParishCollection
    {
        $subParishes = SubParish::all();

        return new SubParishCollection($subParishes);
    }

    public function store(SubParishStoreRequest $request): SubParishResource
    {
        $subParish = SubParish::create($request->validated());

        return new SubParishResource($subParish);
    }

    public function show(Request $request, SubParish $subParish): SubParishResource
    {
        return new SubParishResource($subParish);
    }

    public function update(SubParishUpdateRequest $request, SubParish $subParish): SubParishResource
    {
        $subParish->update($request->validated());

        return new SubParishResource($subParish);
    }
}
