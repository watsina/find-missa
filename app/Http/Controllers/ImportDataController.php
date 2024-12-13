<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Imports\MassesDataImport;
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
        $data = $request->validate([
            'type' => ['required', Rule::in(['masses', 'parishes'])],
            'file' => ['required', File::types(['csv', 'xls', 'xlsx'])],
        ]);

        if ($data['type'] === 'parishes') {
            Excel::import(new ParishChurchDataImport, $request->file);
        } else {
            Excel::import(new MassesDataImport, $request->file);
        }

        return response()->noContent();
    }
}
