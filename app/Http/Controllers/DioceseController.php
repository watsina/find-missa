<?php

namespace App\Http\Controllers;

use App\Models\Diocese;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\DioceseRequest;
use App\Http\Resources\DioceseResource;
use App\Http\Resources\DioceseCollection;

class DioceseController extends Controller
{
    public function index(Request $request): DioceseCollection
    {
        $dioceses = Diocese::all();

        return new DioceseCollection($dioceses);
    }

    public function store(DioceseRequest $request): DioceseResource
    {
        $diocese = Diocese::create($request->validated());

        return new DioceseResource($diocese);
    }

    public function show(Request $request, Diocese $diocese): DioceseResource
    {
        return new DioceseResource($diocese);
    }

    public function update(DioceseRequest $request, Diocese $diocese): DioceseResource
    {
        $diocese->update($request->validated());

        return new DioceseResource($diocese);
    }

    public function destroy(Request $request, Diocese $diocese): Response
    {
        $diocese->delete();

        return response()->noContent();
    }
}
