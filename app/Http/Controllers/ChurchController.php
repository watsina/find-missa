<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\SubParish;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\ChurchRequest;
use App\Http\Resources\ChurchResource;
use App\Http\Resources\ChurchCollection;

class ChurchController extends Controller
{
    public function index(Request $request): ChurchCollection
    {
        $churches = Church::with('activities', 'parishable:id,name,city')->paginate(20);

        return new ChurchCollection($churches);
    }

    public function store(ChurchRequest $request): ChurchResource
    {
        $church = Church::create($request->safe()->except('parishable_id'));
        $data = $request->validated();

        if ($data['type'] === 'sub-parish') {
            $parish = SubParish::find($data['parishable_id']);
        } else {
            $parish = Parish::find($data['parishable_id']);
        }

        $church->parishable()->associate($parish)->save();

        $church->loadMissing('activities', 'parishable:id,name,city');

        return new ChurchResource($church);
    }

    public function show(Request $request, Church $church): ChurchResource
    {
        $church->loadMissing('activities', 'parishable:id,name,city');

        return new ChurchResource($church);
    }

    public function update(ChurchRequest $request, Church $church): ChurchResource
    {
        $church->update($request->safe()->except('parishable_id'));
        $data = $request->validated();

        if (isset($data['parishable_id'])) {
            if ($data['type'] === 'sub-parish') {
                $parish = SubParish::find($data['parishable_id']);
            } else {
                $parish = Parish::find($data['parishable_id']);
            }
            $church->parishable()->associate($parish)->save();
        }

        $church->loadMissing('activities', 'parishable:id,name,city');

        return new ChurchResource($church);
    }

    public function destroy(Request $request, Church $church): Response
    {
        // first make sure the user has deleted all the activites
        $church->delete();

        return response()->noContent();
    }
}
