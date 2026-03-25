<?php

namespace App\Http\Controllers;

use App\Models\Diocese;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rules\File;
use App\Imports\DioceseChurchesImport;

class ImportDioceseChurchesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Diocese $diocese, Request $request)
    {
        Gate::authorize('data.import');

        $request->validate([
            'file' => ['required', File::types(['csv', 'xls', 'xlsx'])],
        ]);

        Excel::import(new DioceseChurchesImport($diocese), $request->file);

        return response()->noContent();
    }
}
