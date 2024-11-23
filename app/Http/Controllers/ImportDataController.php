<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $request->validate([
            'file' => ['required', File::types(['csv', 'xls', 'xlsx'])],
        ]);

        Excel::import(new ParishChurchDataImport, $request->file);

        return response()->noContent();
    }
}
