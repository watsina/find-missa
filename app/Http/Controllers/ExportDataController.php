<?php

namespace App\Http\Controllers;

use ZipArchive;
use App\Models\Diocese;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\DiocesesExport;
use App\Exports\ParishesExport;
use Illuminate\Support\Facades\Log;
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
        $zipName = storage_path("exports/{$folderName}/parishes-export.zip");

        // Ensure the export directory exists
        $exportDir = storage_path("exports/{$folderName}");
        if (! is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $zip = new ZipArchive;
        $openResult = $zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($openResult !== true) {
            Log::error("Failed to create zip file: {$zipName}", ['error_code' => $openResult]);

            return response()->json([
                'message' => 'Failed to create export archive',
            ], 500);
        }

        try {
            // Eager load parishes to avoid N+1
            foreach (Diocese::with(['parishes.churches', 'parishes.subParishes'])->get() as $diocese) {
                $fileName = ucwords(Str::kebab($diocese->name)." {$diocese->type} parishes and sub parishes.xlsx");
                $filePath = "{$folderName}/".$fileName;

                if (! Excel::store(new ParishesExport($diocese), $filePath, 'exports')) {
                    Log::warning("Failed to export diocese: {$diocese->name}");

                    continue;
                }

                $fullPath = storage_path("exports/{$filePath}");
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $fileName);
                }
            }
        } finally {
            $zip->close();
        }

        if (! file_exists($zipName)) {
            return response()->json([
                'message' => 'Export failed - no files were created',
            ], 500);
        }

        return response()->download($zipName)->deleteFileAfterSend(false);
    }
}
