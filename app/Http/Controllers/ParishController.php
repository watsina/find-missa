<?php

namespace App\Http\Controllers;

use App\Models\Parish;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\ParishRequest;
use App\Http\Resources\ParishResource;
use App\Http\Resources\ParishCollection;

class ParishController extends Controller
{
    public function index(Request $request): ParishCollection
    {
        $parishes = Parish::with('diocese')->withCount('churches', 'subParishes')->paginate(20);

        return new ParishCollection($parishes);
    }

    public function store(ParishRequest $request): ParishResource
    {
        $parish = Parish::create($request->validated());

        $parish->loadMissing('diocese');

        return new ParishResource($parish);
    }

    public function show(Request $request, Parish $parish): ParishResource
    {
        $parish->loadMissing('diocese', 'subParishes');

        return new ParishResource($parish);
    }

    public function update(ParishRequest $request, Parish $parish): ParishResource
    {
        $parish->update($request->validated());

        $parish->loadMissing('diocese');

        return new ParishResource($parish);
    }

    public function destroy(Request $request, Parish $parish): Response
    {
        $parish->delete();

        return response()->noContent();
    }
}
