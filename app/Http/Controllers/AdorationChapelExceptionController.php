<?php

namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\Http\Response;
use App\Models\AdorationChapel;
use Illuminate\Support\Facades\Gate;
use App\Models\AdorationChapelException;
use App\Http\Resources\AdorationChapelExceptionResource;
use App\Http\Requests\AdorationChapel\AdorationChapelExceptionRequest;

class AdorationChapelExceptionController extends Controller
{
    public function store(AdorationChapelExceptionRequest $request, Church $church): AdorationChapelExceptionResource
    {
        Gate::authorize('create', [AdorationChapel::class, $church]);

        $chapel = $church->adorationChapel;

        if (! $chapel) {
            abort(404, 'This church does not have an adoration chapel.');
        }

        $exception = $chapel->exceptions()->create([
            ...$request->validated(),
            'team_id' => $church->team_id,
        ]);

        return new AdorationChapelExceptionResource($exception);
    }

    public function update(AdorationChapelExceptionRequest $request, Church $church, AdorationChapelException $exception): AdorationChapelExceptionResource
    {
        Gate::authorize('update', [$church->adorationChapel, $church]);

        $chapel = $church->adorationChapel;

        if (! $chapel || $exception->adoration_chapel_id !== $chapel->id) {
            abort(404, 'Exception not found for this chapel.');
        }

        $exception->update($request->validated());

        return new AdorationChapelExceptionResource($exception);
    }

    public function destroy(Church $church, AdorationChapelException $exception): Response
    {
        Gate::authorize('delete', [$church->adorationChapel, $church]);

        $chapel = $church->adorationChapel;

        if (! $chapel || $exception->adoration_chapel_id !== $chapel->id) {
            abort(404, 'Exception not found for this chapel.');
        }

        $exception->delete();

        return response()->noContent();
    }
}
