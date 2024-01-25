<?php

namespace App\Http\Controllers;

use App\Models\Diocese;
use Illuminate\Http\Request;
use App\Http\Resources\DioceseResource;
use App\Http\Resources\DioceseCollection;
use App\Http\Requests\DioceseStoreRequest;
use App\Http\Requests\DioceseUpdateRequest;

class DioceseController extends Controller
{
    public function index(Request $request): DioceseCollection
    {
        $dioceses = Diocese::all();

        return new DioceseCollection($dioceses);
    }

    public function store(DioceseStoreRequest $request): DioceseResource
    {
        $diocese = Diocese::create($request->validated());

        return new DioceseResource($diocese);
    }

    public function show(Request $request, Diocese $diocese): DioceseResource
    {
        return new DioceseResource($diocese);
    }

    public function update(DioceseUpdateRequest $request, Diocese $diocese): DioceseResource
    {
        $diocese->update($request->validated());

        return new DioceseResource($diocese);
    }
}
