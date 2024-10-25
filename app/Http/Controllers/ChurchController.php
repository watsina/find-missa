<?php

namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\ChurchResource;
use App\Http\Resources\ChurchCollection;
use App\Http\Requests\ChurchStoreRequest;
use App\Http\Requests\ChurchUpdateRequest;

class ChurchController extends Controller
{
    public function index(Request $request): ChurchCollection
    {
        $churches = Church::all();

        return new ChurchCollection($churches);
    }

    public function store(ChurchStoreRequest $request): ChurchResource
    {
        $data = $request->validated();
        $church = Church::create(Arr::except($data, 'parishable_id'));

        return new ChurchResource($church);
    }

    public function show(Request $request, Church $church): ChurchResource
    {
        return new ChurchResource($church);
    }

    public function update(ChurchUpdateRequest $request, Church $church): ChurchResource
    {
        $church->update($request->validated());

        return new ChurchResource($church);
    }

    public function destroy(Request $request, Church $church): Response
    {
        $church->delete();

        return response()->noContent();
    }
}
