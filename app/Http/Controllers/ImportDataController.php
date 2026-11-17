<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Imports\MassesDataImport;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rules\File;
use App\Imports\ParishChurchDataImport;

class ImportDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Gate::authorize('data.import');

        $data = $request->validate([
            'type' => ['required', Rule::in(['masses', 'parishes'])],
            'file' => ['required', File::types(['csv', 'xls', 'xlsx'])],
        ]);

        $team = $request->user()?->currentTeam;

        if ($data['type'] === 'parishes') {
            Excel::import(new ParishChurchDataImport($team), $request->file);
        } else {
            Excel::import(new MassesDataImport($team), $request->file);
        }

        return response()->noContent();
    }
}
