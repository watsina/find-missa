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

        $data = $request->validated();
        $schedules = $data['schedules'] ?? [];
        unset($data['schedules']);

        $chapel = DB::transaction(function () use ($church, $data, $schedules) {
            $chapel = $church->adorationChapel()->create([
                ...$data,
                'team_id' => $church->team_id,
            ]);

            foreach ($schedules as $scheduleData) {
                $dayDetails = $scheduleData['day_details'] ?? [];
                unset($scheduleData['day_details']);

                $schedule = $chapel->schedules()->create($scheduleData);

                foreach ($dayDetails as $detail) {
                    $schedule->dayDetails()->create($detail);
                }
            }

            return $chapel;
        });

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

        $data = $request->validated();
        $schedules = $data['schedules'] ?? null;
        unset($data['schedules']);

        DB::transaction(function () use ($chapel, $data, $schedules) {
            $chapel->update($data);

            // Update schedules if provided
            if ($schedules !== null) {
                // Delete existing schedules and their day details
                foreach ($chapel->schedules as $schedule) {
                    $schedule->dayDetails()->delete();
                }
                $chapel->schedules()->delete();

                // Create new schedules
                foreach ($schedules as $scheduleData) {
                    $dayDetails = $scheduleData['day_details'] ?? [];
                    unset($scheduleData['day_details']);

                    $schedule = $chapel->schedules()->create($scheduleData);

                    foreach ($dayDetails as $detail) {
                        $schedule->dayDetails()->create($detail);
                    }
                }
            }
        });

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
