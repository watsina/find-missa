<?php

namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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

        if (isset($data['longitude']) && isset($data['latitude'])) {
            $data['coordinates'] = DB::raw("ST_SRID(Point({$data['longitude']}, {$data['latitude']}), 4326)");
        }

        unset($data['longitude']);
        unset($data['latitude']);

        $church = Church::create($data);

        return new ChurchResource($church);
    }

    public function show(Request $request, Church $church): ChurchResource
    {
        return new ChurchResource($church);
    }

    public function update(ChurchUpdateRequest $request, Church $church): ChurchResource
    {
        $data = $request->validated();

        if (isset($data['longitude']) && isset($data['latitude'])) {
            $data['coordinates'] = DB::raw("ST_SRID(Point({$data['longitude']}, {$data['latitude']}), 4326)");
        }

        unset($data['longitude']);
        unset($data['latitude']);

        $church->update($data);

        return new ChurchResource($church);
    }

    public function destroy(Request $request, Church $church): Response
    {
        $church->delete();

        return response()->noContent();
    }
}
