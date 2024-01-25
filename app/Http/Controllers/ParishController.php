<?php

namespace App\Http\Controllers;

use App\Models\Parish;
use Illuminate\Http\Request;
use App\Http\Resources\ParishResource;
use App\Http\Resources\ParishCollection;
use App\Http\Requests\ParishStoreRequest;
use App\Http\Requests\ParishUpdateRequest;

class ParishController extends Controller
{
    public function index(Request $request): ParishCollection
    {
        $parishes = Parish::all();

        return new ParishCollection($parishes);
    }

    public function store(ParishStoreRequest $request): ParishResource
    {
        $parish = Parish::create($request->validated());

        return new ParishResource($parish);
    }

    public function show(Request $request, Parish $parish): ParishResource
    {
        return new ParishResource($parish);
    }

    public function update(ParishUpdateRequest $request, Parish $parish): ParishResource
    {
        $parish->update($request->validated());

        return new ParishResource($parish);
    }
}
