<?php

namespace App\Http\Controllers;

use App\Models\Diocese;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\DioceseRequest;
use App\Http\Resources\DioceseResource;
use App\Http\Resources\DioceseCollection;

class DioceseController extends Controller
{
    public function index(Request $request): DioceseCollection
    {
        Gate::authorize('viewAny', Diocese::class);

        $dioceses = Diocese::withCount('parishes')->paginate(20);

        return new DioceseCollection($dioceses);
    }

    public function store(DioceseRequest $request): DioceseResource
    {
        Gate::authorize('create', Diocese::class);

        $diocese = Diocese::create($request->validated());

        $diocese->loadMissing('parishes');

        return new DioceseResource($diocese);
    }

    public function show(Request $request, Diocese $diocese): DioceseResource
    {
        Gate::authorize('view', $diocese);

        $diocese->loadMissing('parishes');

        return new DioceseResource($diocese);
    }

    public function update(DioceseRequest $request, Diocese $diocese): DioceseResource
    {
        Gate::authorize('update', $diocese);

        $diocese->update($request->validated());

        $diocese->loadMissing('parishes');

        return new DioceseResource($diocese);
    }

    public function destroy(Request $request, Diocese $diocese): Response
    {
        Gate::authorize('delete', $diocese);

        $diocese->delete();

        return response()->noContent();
    }
}
