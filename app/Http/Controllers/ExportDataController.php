<?php

namespace App\Http\Controllers;

use ZipArchive;
use App\Models\Diocese;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\DiocesesExport;
use App\Exports\ParishesExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:dioceses,parishes',
        ]);

        if ($data['type'] === 'dioceses') {
            return Excel::download(new DiocesesExport, 'Dioceses.xlsx');
        }

        $folderName = $request->user()->currentTeam->name;
        // $zipName = 'parishes-export.zip';
        $zipName = storage_path("exports/{$folderName}/parishes-export.zip");
        $zip = new ZipArchive;
        $zip->open($zipName, ZipArchive::CREATE);

        foreach (Diocese::all() as $diocese) {
            $fileName = ucwords(Str::kebab($diocese->name)." {$diocese->type} parishes.xlsx");
            if (Excel::store(new ParishesExport($diocese), "{$folderName}/".$fileName, 'exports')) {
                $zip->addFile(storage_path("exports/{$folderName}/".$fileName), $fileName);
            }
        }
        $zip->close();

        return response()->download($zipName);
    }
}
