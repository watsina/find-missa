<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Models\GlobalActivityTimeException;
use App\Http\Requests\StoreGlobalActivityTimeExceptionRequest;
use App\Http\Requests\UpdateGlobalActivityTimeExceptionRequest;

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
    public function store(StoreGlobalActivityTimeExceptionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(GlobalActivityTimeException $globalActivityTimeException)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGlobalActivityTimeExceptionRequest $request, GlobalActivityTimeException $globalActivityTimeException)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GlobalActivityTimeException $globalActivityTimeException)
    {
        //
    }
}
