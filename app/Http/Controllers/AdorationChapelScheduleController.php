<?php

namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\Http\Response;
use App\Models\AdorationChapel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\AdorationChapelSchedule;
use App\Http\Resources\AdorationChapelScheduleResource;
use App\Http\Requests\AdorationChapel\StoreAdorationChapelScheduleRequest;
use App\Http\Requests\AdorationChapel\UpdateAdorationChapelScheduleRequest;

class AdorationChapelScheduleController extends Controller
{
    public function store(StoreAdorationChapelScheduleRequest $request, Church $church): AdorationChapelScheduleResource
    {
        Gate::authorize('create', [AdorationChapel::class, $church]);

        $chapel = $church->adorationChapel;

        if (! $chapel) {
            abort(404, 'This church does not have an adoration chapel.');
        }

        $data = $request->validated();
        $dayDetails = $data['day_details'] ?? [];
        unset($data['day_details']);

        $schedule = DB::transaction(function () use ($chapel, $data, $dayDetails) {
            $schedule = $chapel->schedules()->create($data);

            foreach ($dayDetails as $detail) {
                $schedule->dayDetails()->create($detail);
            }

            return $schedule;
        });

        $schedule->load('dayDetails');

        return new AdorationChapelScheduleResource($schedule);
    }

    public function update(UpdateAdorationChapelScheduleRequest $request, Church $church, AdorationChapelSchedule $schedule): AdorationChapelScheduleResource
    {
        Gate::authorize('update', [$church->adorationChapel, $church]);

        $chapel = $church->adorationChapel;

        if (! $chapel || $schedule->adoration_chapel_id !== $chapel->id) {
            abort(404, 'Schedule not found for this chapel.');
        }

        $data = $request->validated();
        $dayDetails = $data['day_details'] ?? null;
        unset($data['day_details']);

        DB::transaction(function () use ($schedule, $data, $dayDetails) {
            $schedule->update($data);

            if ($dayDetails !== null) {
                $schedule->dayDetails()->delete();

                foreach ($dayDetails as $detail) {
                    $schedule->dayDetails()->create($detail);
                }
            }
        });

        $schedule->load('dayDetails');

        return new AdorationChapelScheduleResource($schedule);
    }

    public function destroy(Church $church, AdorationChapelSchedule $schedule): Response
    {
        Gate::authorize('delete', [$church->adorationChapel, $church]);

        $chapel = $church->adorationChapel;

        if (! $chapel || $schedule->adoration_chapel_id !== $chapel->id) {
            abort(404, 'Schedule not found for this chapel.');
        }

        DB::transaction(function () use ($schedule) {
            $schedule->dayDetails()->delete();
            $schedule->delete();
        });

        return response()->noContent();
    }
}
