<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParishChurchDataImport;

class UploadTestDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Excel::import(new ParishChurchDataImport, $request->file);

        return response()->noContent();
    }
}
