<?php

namespace App\Http\Controllers\AppUsers;

use App\Models\Church;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChurchDetailsResource;

class GetChurchDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Church $church)
    {
        $church->loadMissing('activities', 'parishable');

        return new ChurchDetailsResource($church);
    }
}
