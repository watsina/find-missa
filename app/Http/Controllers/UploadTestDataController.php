<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ChurchImport;
use Maatwebsite\Excel\Facades\Excel;

class UploadTestDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Excel::import(new ChurchImport, $request->file);

        $request->session()->flash('flash.banner', 'Data was imported successfully.');
    }
}
