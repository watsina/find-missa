<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Models\GlobalActivityTimeException;
use App\Http\Requests\GlobalActivityTimeExceptionRequest;

class GlobalActivityTimeExceptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', GlobalActivityTimeException::class);

        return GlobalActivityTimeException::all()->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GlobalActivityTimeExceptionRequest $request)
    {
        Gate::authorize('create', GlobalActivityTimeException::class);

        $activityTimeException = GlobalActivityTimeException::create($request->validated());

        return $activityTimeException->toResource();
    }

    /**
     * Display the specified resource.
     */
    public function show(GlobalActivityTimeException $globalActivityTimeException)
    {
        Gate::authorize('view', $globalActivityTimeException);

        return $globalActivityTimeException->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GlobalActivityTimeExceptionRequest $request, GlobalActivityTimeException $globalActivityTimeException)
    {
        Gate::authorize('update', $globalActivityTimeException);

        $globalActivityTimeException->update($request->validated());

        return $globalActivityTimeException->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GlobalActivityTimeException $globalActivityTimeException)
    {
        Gate::authorize('delete', $globalActivityTimeException);

        $globalActivityTimeException->delete();

        return response()->noContent();
    }
}
