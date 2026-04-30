<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\DiocesesExport;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\DataExportZipService;

class ExportDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, DataExportZipService $service)
    {
        Gate::authorize('data.export');

        $data = $request->validate([
            'type' => ['required', 'string', 'in:dioceses,parishes'],
        ]);

        if ($data['type'] === 'dioceses') {
            return Excel::download(new DiocesesExport, 'Dioceses.xlsx');
        }

        return $service->exportParishesAsZip($request->user()->currentTeam);
    }
}
