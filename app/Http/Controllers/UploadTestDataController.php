<?php

namespace App\Http\Controllers;

use App\Imports\DataImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UploadTestDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Excel::import(new DataImport, $request->file);

        return response()->noContent();
    }
}
