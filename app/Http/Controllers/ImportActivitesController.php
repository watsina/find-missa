<?php

namespace App\Http\Controllers;

use App\Models\Diocese;
use Illuminate\Http\Request;
use App\Imports\ActivitiesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rules\File;

class ImportActivitesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Diocese $diocese, Request $request)
    {
        $request->validate([
            'file' => ['required', File::types(['csv', 'xls', 'xlsx'])],
        ]);

        Excel::import(new ActivitiesImport($diocese), $request->file);

        return response()->noContent();
    }
}
