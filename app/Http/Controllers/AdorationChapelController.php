<?php

namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AdorationChapel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\AdorationChapelResource;
use App\Http\Requests\AdorationChapel\StoreAdorationChapelRequest;
use App\Http\Requests\AdorationChapel\UpdateAdorationChapelRequest;

class AdorationChapelController extends Controller
{
    public function show(Request $request, Church $church): AdorationChapelResource
    {
        Gate::authorize('view', [$church->adorationChapel, $church]);

        $chapel = $church->adorationChapel;

        if (! $chapel) {
            abort(404, 'This church does not have an adoration chapel.');
        }

        $chapel->loadMissing('schedules.dayDetails', 'exceptions');

        return new AdorationChapelResource($chapel);
    }

    public function store(StoreAdorationChapelRequest $request, Church $church): AdorationChapelResource
    {
        Gate::authorize('create', [AdorationChapel::class, $church]);

        if ($church->adorationChapel) {
            abort(422, 'This church already has an adoration chapel.');
        }

        $chapel = $church->adorationChapel()->create([
            ...$request->validated(),
            'team_id' => $church->team_id,
        ]);

        $chapel->load('schedules.dayDetails', 'exceptions');

        return new AdorationChapelResource($chapel);
    }

    public function update(UpdateAdorationChapelRequest $request, Church $church): AdorationChapelResource
    {
        Gate::authorize('update', [$church->adorationChapel, $church]);

        $chapel = $church->adorationChapel;

        if (! $chapel) {
            abort(404, 'This church does not have an adoration chapel.');
        }

        $chapel->update($request->validated());

        $chapel->load('schedules.dayDetails', 'exceptions');

        return new AdorationChapelResource($chapel);
    }

    public function destroy(Request $request, Church $church): Response
    {
        Gate::authorize('delete', [$church->adorationChapel, $church]);

        $chapel = $church->adorationChapel;

        if (! $chapel) {
            abort(404, 'This church does not have an adoration chapel.');
        }

        DB::transaction(function () use ($chapel) {
            foreach ($chapel->schedules as $schedule) {
                $schedule->dayDetails()->delete();
            }
            $chapel->schedules()->delete();
            $chapel->exceptions()->delete();
            $chapel->delete();
        });

        return response()->noContent();
    }
}
